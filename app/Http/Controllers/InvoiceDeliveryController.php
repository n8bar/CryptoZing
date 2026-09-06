<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Services\InvoiceDeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class InvoiceDeliveryController extends Controller
{
    public function __construct(private readonly InvoiceDeliveryService $deliveries)
    {
    }

    public function updateDraft(Request $request, Invoice $invoice): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $message = $validated['message'] ?? null;
        $message = is_string($message) ? trim($message) : null;

        $invoice->forceFill([
            'delivery_message_draft' => $message !== '' ? $message : null,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json([
                'saved' => true,
                'message' => $invoice->delivery_message_draft,
            ]);
        }

        return back()->with('status', 'Delivery note draft saved.');
    }

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'message' => ['nullable','string','max:1000'],
            'cc_self' => ['nullable','boolean'],
        ]);

        if (!$invoice->client || empty($invoice->client->email)) {
            return back()->with('status', 'Add a client email before sending.');
        }

        if (!$invoice->public_enabled || !$invoice->public_token) {
            return back()->with('status', 'Enable the public share link before sending.');
        }

        $recipient = $invoice->client->email;
        $cc = $request->boolean('cc_self') ? $invoice->user->email : null;

        $message = $validated['message'] ?? null;

        // A second send of an already-delivered invoice is deliberate: it goes
        // out as its own delivery, and only the short manual cooldown holds it
        // off (#182). The refusal is recorded so the delivery log stays truthful.
        if ($this->deliveries->hasSentDeliveryTo($invoice, 'send', $recipient)) {
            $delivery = $this->deliveries->queueResend($invoice, 'send', $recipient, $cc, $message)
                ?? $this->deliveries->skip(
                    $invoice,
                    'send',
                    $recipient,
                    'Invoice email skipped because the same notice was already queued or sent recently.',
                    $cc,
                    $message,
                    'resend_cooldown_' . Str::uuid(),
                );
        } else {
            $delivery = $this->deliveries->queue($invoice, 'send', $recipient, $cc, $message);
        }

        if ($delivery->status === 'queued') {
            $updates = ['delivery_message_draft' => null];
            if ($invoice->status === 'draft') {
                $updates['status'] = 'sent';
            }
            $invoice->forceFill($updates)->save();
        }

        $statusMessage = $delivery->status === 'queued'
            ? 'Invoice email queued.'
            : ($delivery->error_message ?: 'Invoice email skipped.');

        if ($request->boolean('getting_started')) {
            return redirect()
                ->route('getting-started.start')
                ->with('status', $statusMessage);
        }

        return back()->with('status', $statusMessage);
    }

    public function storeReceipt(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if (!$invoice->client || empty($invoice->client->email)) {
            return back()->with('status', 'Add a client email before sending a receipt.');
        }

        if ($invoice->status !== 'paid') {
            return back()->with('status', 'Only paid invoices can send a receipt.');
        }

        if ($invoice->receiptIsBlockedByCorrectionState()) {
            return back()->with('error', 'Resolve the ignored or reattributed payment before sending the receipt.');
        }

        $delivery = $this->deliveries->queue(
            $invoice,
            'receipt',
            $invoice->client->email
        );

        $statusMessage = $delivery->status === 'queued'
            ? 'Receipt queued.'
            : ($delivery->error_message ?: 'Receipt skipped.');

        if ($request->boolean('getting_started')) {
            return redirect()
                ->route('getting-started.start')
                ->with('status', $statusMessage);
        }

        return back()->with('status', $statusMessage);
    }

    public function resendReceipt(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if (! $invoice->client || empty($invoice->client->email)) {
            return back()->with('status', 'No client email on file.');
        }

        if ($invoice->status !== 'paid') {
            return back()->with('status', 'Only paid invoices can send a receipt.');
        }

        $hasSentReceipt = $invoice->deliveries()
            ->where('type', 'receipt')
            ->whereIn('status', ['queued', 'sending', 'sent'])
            ->exists();

        if (! $hasSentReceipt) {
            return back()->with('status', 'No receipt has been sent yet.');
        }

        $delivery = $this->deliveries->queueResend($invoice, 'receipt', $invoice->client->email);

        if ($delivery === null) {
            $cooldown = $this->deliveries->manualSendCooldownMinutes();
            return back()->with('status', "Receipt was sent recently. Please wait {$cooldown} minutes before resending.");
        }

        return back()->with('status', 'Receipt resend queued.');
    }

    public function resendDelivery(Request $request, Invoice $invoice, InvoiceDelivery $delivery): RedirectResponse
    {
        $this->authorize('update', $invoice);

        if ($delivery->invoice_id !== $invoice->id) {
            abort(404);
        }

        if ($delivery->status !== 'failed') {
            return back()->with('status', 'Only failed deliveries can be resent.');
        }

        $resend = $this->deliveries->queueResend($invoice, $delivery->type, $delivery->recipient);

        if ($resend === null) {
            $cooldown = $this->deliveries->manualSendCooldownMinutes();
            return back()->with('status', "That notice was sent recently. Please wait {$cooldown} minutes before resending.");
        }

        return back()->with('status', 'Delivery resend queued.');
    }
}
