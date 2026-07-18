<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\InvoiceDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MailgunWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        if (! $this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        $eventData = $request->input('event-data', []);
        $event = $eventData['event'] ?? null;
        $messageId = $eventData['message']['headers']['message-id'] ?? null;
        $deliveryId = $eventData['user-variables']['delivery_id'] ?? null;

        // Resolve by the delivery id we tag onto every message — matches even when
        // provider_message_id was never persisted (e.g. a stuck `sending` row). Fall
        // back to the provider message id for older, untagged messages.
        $delivery = null;
        if ($deliveryId !== null && $deliveryId !== '') {
            $delivery = InvoiceDelivery::find($deliveryId);
        }
        if (! $delivery && $messageId) {
            $delivery = InvoiceDelivery::where('provider_message_id', $messageId)->first();
        }

        if (! $delivery) {
            return response()->json(['ok' => true]);
        }

        match ($event) {
            // `accepted` confirms the provider took the message — enough to resolve a
            // delivery that was sent but never recorded `sent`; `delivered` confirms it too.
            'accepted', 'delivered' => $delivery->update([
                'status' => 'sent',
                'sent_at' => $delivery->sent_at ?? now(),
                'provider_message_id' => $delivery->provider_message_id ?: $messageId,
                'error_code' => null,
                'error_message' => null,
            ]),
            'failed', 'bounced' => $this->handleFailure($delivery, $eventData, $messageId),
            default => null,
        };

        return response()->json(['ok' => true]);
    }

    private function verifySignature(Request $request): bool
    {
        $signature = $request->input('signature', []);
        $timestamp = (string) ($signature['timestamp'] ?? '');
        $token = (string) ($signature['token'] ?? '');
        $provided = (string) ($signature['signature'] ?? '');
        $signingKey = config('services.mailgun.webhook_signing_key');

        if (! $signingKey || ! $timestamp || ! $token || ! $provided) {
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . $token, $signingKey);

        return hash_equals($expected, $provided);
    }

    private function handleFailure(InvoiceDelivery $delivery, array $eventData, ?string $messageId = null): void
    {
        $status = $eventData['delivery-status'] ?? [];
        $reason = $status['description'] ?? $status['message'] ?? 'Delivery failed.';
        $code = (string) ($status['code'] ?? '');

        $delivery->update([
            'status' => 'failed',
            'provider_message_id' => $delivery->provider_message_id ?: $messageId,
            'error_code' => $code ?: null,
            'error_message' => $reason,
        ]);

        Log::warning('invoice_delivery.mailgun_failed', [
            'delivery_id' => $delivery->id,
            'invoice_id' => $delivery->invoice_id,
            'type' => $delivery->type,
            'recipient' => $delivery->recipient,
            'reason' => $reason,
            'code' => $code ?: null,
        ]);
    }
}
