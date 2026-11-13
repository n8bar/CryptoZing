<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\ApiIdempotencyKey;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Services\BtcRate;
use App\Services\InvoiceCreator;
use App\Services\InvoiceDeliveryService;
use App\Services\InvoiceLines;
use App\Services\WalletKeyLineage;
use App\Support\ApiError;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** The developer API's invoice endpoints; see docs/API.md. */
class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceCreator $creator,
        private readonly InvoiceDeliveryService $deliveries,
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $key = (string) $request->header('Idempotency-Key', '');
        if ($key === '' || strlen($key) > 255) {
            throw ValidationException::withMessages(['Idempotency-Key' => 'The Idempotency-Key header is required, up to 255 characters.']);
        }

        $hash = hash('sha256', json_encode($request->json()->all()));
        if ($earlier = $this->earlierRequest($user, $key, $hash)) {
            return $earlier;
        }

        $data = $this->validated($request, [
            'client'       => ['required', 'array'],
            'client.name'  => ['required', 'string', 'max:255'],
            'client.email' => ['required', 'email', 'max:255'],
            'due_date'     => ['nullable', 'date'],
            'send'         => ['nullable', 'boolean'],
        ] + InvoiceLines::rules());

        $wallet = $user->walletSetting;
        $preparedLineage = null;
        try {
            $preparedLineage = $wallet ? app(WalletKeyLineage::class)->previewNextAssignment($wallet) : null;
        } catch (\Throwable) {
        }
        if (! $preparedLineage) {
            throw ValidationException::withMessages(['wallet' => 'Connect a working wallet in the app before creating invoices.']);
        }

        if ($request->boolean('send') && InvoiceLines::total($data['lines']) < 0) {
            throw ValidationException::withMessages(['lines' => Invoice::SEND_NEEDS_ZERO_OR_MORE]);
        }

        $rate = BtcRate::current()['rate_usd'] ?? null;
        if (! $rate) {
            return ApiError::unavailable('No BTC rate is available right now. Try again shortly.');
        }

        $fields = $this->creator->applyDefaults($user, [
            'client_id'    => $this->client($user, $data['client'])->id,
            'number'       => Invoice::nextNumberForUser($user->id),
            'status'       => 'draft',
            'invoice_date' => now()->toDateString(),
            'due_date'     => $data['due_date'] ?? null,
            'amount_usd'   => InvoiceLines::total($data['lines']),
            'btc_rate'     => $rate,
        ]);

        for ($attempt = 1; ; $attempt++) {
            try {
                $invoice = DB::transaction(function () use ($fields, $wallet, $user, $preparedLineage, $data, $key, $hash) {
                    $invoice = $this->creator->create($fields, $wallet, $user->id, $preparedLineage, $data['lines']);
                    $this->priceBtc($invoice);
                    ApiIdempotencyKey::create(['user_id' => $user->id, 'key' => $key, 'request_hash' => $hash, 'invoice_id' => $invoice->id]);

                    return $invoice;
                });
                break;
            } catch (UniqueConstraintViolationException $e) {
                // A simultaneous create took this number: take the next one, as the app's create screen does.
                if ($attempt < 3 && Invoice::isNumberCollision($e)) {
                    $fields['number'] = Invoice::nextNumberForUser($user->id);
                    continue;
                }

                // A simultaneous request with the same key won; answer as a repeat.
                return $this->earlierRequest($user, $key, $hash) ?? throw $e;
            }
        }

        if ($request->boolean('send')) {
            $this->send($invoice);
        }

        return (new InvoiceResource($invoice->fresh()))->response()->setStatusCode(201);
    }

    public function show(Request $request, string $id): InvoiceResource
    {
        return new InvoiceResource($this->find($request, $id));
    }

    public function update(Request $request, string $id): JsonResponse|InvoiceResource
    {
        $invoice = $this->find($request, $id);
        if ($invoice->public_enabled) {
            return ApiError::response('not_draft', 'The public link is on, so this invoice cannot change. Delete it and create a new one.', 409);
        }

        $data = $this->validated($request, [
            'client'       => ['sometimes', 'array'],
            'client.name'  => ['required_with:client', 'string', 'max:255'],
            'client.email' => ['required_with:client', 'email', 'max:255'],
            'due_date'     => ['sometimes', 'nullable', 'date'],
        ] + array_map(fn ($rules) => ['sometimes', ...$rules], InvoiceLines::rules()));

        DB::transaction(function () use ($invoice, $data, $request) {
            if (isset($data['client'])) {
                $invoice->client_id = $this->client($request->user(), $data['client'])->id;
            }
            if (array_key_exists('due_date', $data)) {
                $invoice->due_date = $data['due_date'];
            }
            $invoice->save();

            if (isset($data['lines'])) {
                InvoiceLines::replaceFor($invoice, $data['lines']);
                $this->priceBtc($invoice->refresh());
                $this->creator->syncAddress($invoice, $request->user()->walletSetting);
            }
        });

        return new InvoiceResource($invoice->fresh());
    }

    public function destroy(Request $request, string $id): Response
    {
        $this->find($request, $id)->delete();

        return response()->noContent();
    }

    public function sendNow(Request $request, string $id): JsonResponse
    {
        $invoice = $this->find($request, $id);
        $this->send($invoice);

        return (new InvoiceResource($invoice->fresh()))->response()->setStatusCode(202);
    }

    /** Price the BTC request from the stored total, which the lines set. */
    private function priceBtc(Invoice $invoice): void
    {
        if ((float) $invoice->btc_rate > 0) {
            $invoice->forceFill(['amount_btc' => max(0, round((float) $invoice->amount_usd / (float) $invoice->btc_rate, 8))])->save();
        }
    }

    /** Turn the public link on, since the email carries it, and queue the invoice email. Nothing changes when it can't go. */
    private function send(Invoice $invoice): void
    {
        if (empty($invoice->client?->email)) {
            throw ValidationException::withMessages(['client' => 'Add a client email before sending.']);
        }
        if (! $invoice->canBeSent()) {
            throw ValidationException::withMessages(['lines' => Invoice::SEND_NEEDS_ZERO_OR_MORE]);
        }

        if (! $invoice->public_enabled) {
            $invoice->enablePublicShare();
        }

        $this->deliveries->sendInvoice($invoice, $invoice->client->email);
    }

    /** One of this key's issuer's invoices, by its API id; anything else is not found. */
    private function find(Request $request, string $id): Invoice
    {
        return Invoice::ownedBy($request->user())->where('api_id', $id)->firstOrFail();
    }

    /** The issuer's client with this email, or a new one. */
    private function client(User $user, array $client): Client
    {
        return Client::firstOrCreate(
            ['user_id' => $user->id, 'email' => $client['email']],
            ['name' => $client['name']],
        );
    }

    private function validated(Request $request, array $rules): array
    {
        return validator($request->json()->all(), $rules, InvoiceLines::messages(), InvoiceLines::attributes())
            ->after(fn ($v) => InvoiceLines::validateTargets($v))
            ->validate();
    }

    /** The response for a repeated Idempotency-Key: the first invoice, or a conflict when the body differs. */
    private function earlierRequest(User $user, string $key, string $hash): ?JsonResponse
    {
        $earlier = ApiIdempotencyKey::where('user_id', $user->id)->where('key', $key)->first();
        if (! $earlier) {
            return null;
        }
        if (! hash_equals($earlier->request_hash, $hash)) {
            return ApiError::response('conflict', 'This Idempotency-Key was used with a different request.', 409);
        }

        return (new InvoiceResource($earlier->invoice))->response()->setStatusCode(200);
    }
}
