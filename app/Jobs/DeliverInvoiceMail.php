<?php

namespace App\Jobs;

use App\Mail\InvoiceOverpaymentClientMail;
use App\Mail\InvoiceOverpaymentIssuerMail;
use App\Mail\InvoicePastDueClientMail;
use App\Mail\InvoicePastDueIssuerMail;
use App\Mail\InvoicePaymentAcknowledgmentClientMail;
use App\Mail\InvoicePaymentAcknowledgmentIssuerMail;
use App\Mail\InvoicePaidReceiptMail;
use App\Mail\InvoiceReadyMail;
use App\Mail\InvoiceUnderpaymentClientMail;
use App\Mail\InvoiceUnderpaymentIssuerMail;
use App\Mail\InvoiceIssuerPaidNoticeMail;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\InvoicePayment;
use App\Services\InvoiceDeliveryService;
use App\Services\MailAlias;
use App\Services\MailgunEventLookup;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mime\Email;

class DeliverInvoiceMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var int[] Seconds to wait before each retry attempt. */
    public array $backoff = [60, 300];

    public function __construct(public InvoiceDelivery $delivery)
    {
    }

    public function handle(MailAlias $mailAlias, InvoiceDeliveryService $deliveries): void
    {
        $delivery = $this->delivery->fresh();
        if (!$delivery) {
            return;
        }

        $sendLock = Cache::lock($this->sendLockName($deliveries->intentKeyForDelivery($delivery)), 30);

        if (! $sendLock->get()) {
            Log::info('invoice_delivery.send_locked', [
                'delivery_id' => $delivery->id,
                'invoice_id' => $delivery->invoice_id,
                'type' => $delivery->type,
                'recipient' => $delivery->recipient,
            ]);
            return;
        }

        try {
            $delivery = $this->delivery->fresh();
            if (! $delivery) {
                return;
            }

            if ($delivery->status === 'sending') {
                // Leftover from an interrupted prior attempt. With a provider we can
                // query (Mailgun), reconcile; otherwise no-op — we can't verify whether
                // it sent, and a throw→fail→resend could duplicate a delivered message.
                if (config('mail.default') !== 'mailgun') {
                    Log::info('invoice_delivery.send_already_claimed', [
                        'delivery_id' => $delivery->id,
                        'invoice_id' => $delivery->invoice_id,
                        'type' => $delivery->type,
                        'recipient' => $delivery->recipient,
                    ]);
                    return;
                }
                if ($this->reconcileStuckSending($delivery)) {
                    return; // provider confirms it went out → now `sent`
                }
                // Not confirmed — keep the job alive so the queue retries; failed()
                // records terminal `failed` once tries are exhausted.
                throw new \RuntimeException(
                    "Invoice delivery {$delivery->id} stuck in sending; awaiting provider confirmation."
                );
            }

            if ($delivery->status !== 'queued') {
                return;
            }

            $invoice = $delivery->invoice()->with(['client', 'user', 'payments', 'sourcePayments'])->first();
            if (! $invoice || ! $invoice->client) {
                $delivery->update([
                    'status' => 'failed',
                    'error_message' => 'Missing invoice/client context.',
                ]);
                return;
            }

            if ($skipReason = $this->shouldSkipDelivery($delivery, $invoice, $deliveries)) {
                $delivery->update([
                    'status' => 'skipped',
                    'error_message' => $skipReason,
                ]);
                return;
            }

            if ($duplicateReason = $this->duplicateSendReason($delivery)) {
                $delivery->update([
                    'status' => 'skipped',
                    'error_message' => $duplicateReason,
                ]);
                return;
            }

            $claimed = InvoiceDelivery::query()
                ->whereKey($delivery->id)
                ->where('status', 'queued')
                ->update([
                    'status' => 'sending',
                    'error_code' => null,
                    'error_message' => null,
                ]);

            if ($claimed !== 1) {
                return;
            }

            $delivery->refresh();
        } finally {
            $sendLock->release();
        }

        $paymentAcknowledgment = $this->paymentForAcknowledgment($invoice, $delivery);

        $mailable = match ($delivery->type) {
            'payment_acknowledgment_client' => new InvoicePaymentAcknowledgmentClientMail(
                $invoice,
                $delivery,
                $paymentAcknowledgment
            ),
            'payment_acknowledgment_issuer' => new InvoicePaymentAcknowledgmentIssuerMail(
                $invoice,
                $delivery,
                $paymentAcknowledgment
            ),
            'receipt' => new InvoicePaidReceiptMail($invoice, $delivery),
            'issuer_paid_notice' => new InvoiceIssuerPaidNoticeMail($invoice, $delivery),
            'past_due_issuer' => new InvoicePastDueIssuerMail($invoice, $delivery),
            'past_due_client' => new InvoicePastDueClientMail($invoice, $delivery),
            'client_overpay_alert' => new InvoiceOverpaymentClientMail($invoice, $delivery),
            'issuer_overpay_alert' => new InvoiceOverpaymentIssuerMail($invoice, $delivery),
            'client_underpay_alert' => new InvoiceUnderpaymentClientMail($invoice, $delivery),
            'issuer_underpay_alert' => new InvoiceUnderpaymentIssuerMail($invoice, $delivery),
            default => new InvoiceReadyMail($invoice, $delivery),
        };

        // Tag the outbound message with the delivery id (Mailgun `v:delivery_id`) so
        // its provider outcome is matchable from webhooks/events even if we never
        // persist a provider_message_id. See NOTIFICATIONS.md item 17.
        $mailable->withSymfonyMessage(function (Email $message) use ($delivery) {
            $message->getHeaders()->add(new MetadataHeader('delivery_id', (string) $delivery->id));
        });

        // Only the send itself is retryable. A failure here releases the claim
        // back to `queued` so a retry re-attempts; terminal `failed` is recorded
        // only once retries are exhausted, in failed().
        try {
            $mailer = Mail::to($mailAlias->convert($delivery->recipient));
            if ($delivery->cc) {
                $mailer->cc($mailAlias->convert($delivery->cc));
            }
            $sentMessage = $mailer->send($mailable);
        } catch (\Throwable $e) {
            $delivery->update([
                'status' => 'queued',
                'error_code' => (string) $e->getCode(),
                'error_message' => $e->getMessage(),
            ]);
            Log::warning('Invoice delivery send failed; will retry', [
                'delivery_id' => $delivery->id,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        // Send succeeded — record it OUTSIDE the retryable catch. A transient
        // failure here must not re-send, so retry the write a few times; if it
        // still fails, throw and let the job re-run reconcile the `sending` row
        // against the provider (no re-send).
        for ($attempt = 1; ; $attempt++) {
            try {
                $delivery->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'provider_message_id' => $sentMessage?->getMessageId(),
                    'error_code' => null,
                    'error_message' => null,
                ]);
                break;
            } catch (\Throwable $e) {
                if ($attempt >= 3) {
                    Log::warning('invoice_delivery.sent_write_failed', [
                        'delivery_id' => $delivery->id,
                        'error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
                usleep(100_000);
            }
        }
        Log::info('invoice_delivery.sent', [
            'delivery_id' => $delivery->id,
            'invoice_id' => $invoice->id,
            'type' => $delivery->type,
            'recipient' => $delivery->recipient,
        ]);
    }

    /**
     * Resolve a delivery stranded in `sending` by an interrupted prior attempt:
     * ask the provider whether the message actually went out. Returns true and
     * marks `sent` if confirmed; false otherwise (caller retries). Never re-sends.
     */
    private function reconcileStuckSending(InvoiceDelivery $delivery): bool
    {
        if (app(MailgunEventLookup::class)->wasAccepted($delivery) === true) {
            $delivery->update([
                'status' => 'sent',
                'sent_at' => $delivery->sent_at ?? now(),
                'error_code' => null,
                'error_message' => null,
            ]);
            Log::info('invoice_delivery.reconciled_sent', [
                'delivery_id' => $delivery->id,
                'invoice_id' => $delivery->invoice_id,
                'type' => $delivery->type,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Invoked by the queue after the retry budget is exhausted. Only here does a
     * delivery become terminally `failed`.
     */
    public function failed(\Throwable $e): void
    {
        $delivery = $this->delivery->fresh();
        if (! $delivery || ! in_array($delivery->status, ['queued', 'sending'], true)) {
            return;
        }

        $delivery->update([
            'status' => 'failed',
            'error_code' => (string) $e->getCode(),
            'error_message' => $e->getMessage(),
        ]);
        Log::error('Invoice delivery failed after retries', [
            'delivery_id' => $delivery->id,
            'invoice_id' => $delivery->invoice_id,
            'type' => $delivery->type,
            'error' => $e->getMessage(),
        ]);
    }

    private function duplicateSendReason(InvoiceDelivery $delivery): ?string
    {
        $query = InvoiceDelivery::query()
            ->where('invoice_id', $delivery->invoice_id)
            ->where('user_id', $delivery->user_id)
            ->where('type', $delivery->type)
            ->whereRaw('LOWER(TRIM(recipient)) = ?', [strtolower(trim($delivery->recipient))])
            ->where('id', '!=', $delivery->id)
            ->whereIn('status', ['sending', 'sent'])
            ->orderByDesc('id');

        $normalizedContextKey = $this->normalizeContextKey($delivery->context_key);

        if ($normalizedContextKey === null) {
            $query->whereNull('context_key');
        } else {
            $query->whereRaw('LOWER(TRIM(context_key)) = ?', [$normalizedContextKey]);
        }

        $existing = $query->first();

        if (! $existing) {
            return null;
        }

        return $existing->status === 'sent'
            ? 'A matching delivery has already been sent.'
            : 'A matching delivery send is already in progress.';
    }

    private function sendLockName(string $intentKey): string
    {
        return 'invoice-delivery-send:' . $intentKey;
    }

    private function shouldSkipDelivery(
        InvoiceDelivery $delivery,
        \App\Models\Invoice $invoice,
        InvoiceDeliveryService $deliveries
    ): ?string
    {
        if ($delivery->status !== 'queued') {
            return 'Delivery no longer queued.';
        }

        if (! $deliveries->outboundEnabled()) {
            return 'Outbound mail is temporarily disabled.';
        }

        if ($delivery->type === 'send') {
            $currentRecipient = trim((string) ($invoice->client?->email ?? ''));
            if ($currentRecipient === '') {
                return 'Client email missing before send.';
            }

            if (strcasecmp($currentRecipient, trim((string) $delivery->recipient)) !== 0) {
                return 'Recipient no longer matches the current client email.';
            }

            if (! $invoice->public_enabled || ! $invoice->public_token) {
                return 'Public share link disabled before send.';
            }
        }

        $paymentAcknowledgmentTypes = ['payment_acknowledgment_client', 'payment_acknowledgment_issuer'];
        if (in_array($delivery->type, $paymentAcknowledgmentTypes, true)) {
            $payment = $this->paymentForAcknowledgment($invoice, $delivery);
            if (! $payment) {
                return 'Detected payment no longer matches an active payment on this invoice.';
            }

            if ($delivery->type === 'payment_acknowledgment_client') {
                $currentRecipient = trim((string) ($invoice->client?->email ?? ''));
                if ($currentRecipient === '') {
                    return 'Client email missing before send.';
                }

                if (strcasecmp($currentRecipient, trim((string) $delivery->recipient)) !== 0) {
                    return 'Recipient no longer matches the current client email.';
                }
            }

            if ($delivery->type === 'payment_acknowledgment_issuer') {
                $currentRecipient = trim((string) ($invoice->user?->email ?? ''));
                if ($currentRecipient === '') {
                    return 'Issuer email missing before send.';
                }

                if (strcasecmp($currentRecipient, trim((string) $delivery->recipient)) !== 0) {
                    return 'Recipient no longer matches the current issuer email.';
                }
            }
        }

        $paidTypes = ['receipt', 'issuer_paid_notice'];
        if (in_array($delivery->type, $paidTypes, true) && $invoice->status !== 'paid') {
            return 'Invoice no longer paid.';
        }

        $overpayTypes = ['client_overpay_alert', 'issuer_overpay_alert'];
        if (in_array($delivery->type, $overpayTypes, true) && !$invoice->requiresClientOverpayAlert()) {
            return 'Overpayment resolved before send.';
        }

        $underpayTypes = ['client_underpay_alert', 'issuer_underpay_alert'];
        if (in_array($delivery->type, $underpayTypes, true) && !$invoice->requiresClientUnderpayAlert()) {
            return 'Underpayment resolved before send.';
        }

        $pastDueTypes = ['past_due_issuer', 'past_due_client'];
        if (in_array($delivery->type, $pastDueTypes, true) && in_array($invoice->status, ['paid', 'void'], true)) {
            return 'Invoice settled before past-due alert.';
        }

        return null;
    }

    private function paymentForAcknowledgment(Invoice $invoice, InvoiceDelivery $delivery): ?InvoicePayment
    {
        if (! in_array($delivery->type, ['payment_acknowledgment_client', 'payment_acknowledgment_issuer'], true)) {
            return null;
        }

        if (! filled($delivery->context_key)) {
            return null;
        }

        return $invoice->activeSourcePaymentByTxid($delivery->context_key);
    }

    private function normalizeContextKey(?string $contextKey): ?string
    {
        if ($contextKey === null) {
            return null;
        }

        $normalized = strtolower(trim($contextKey));

        return $normalized === '' ? null : $normalized;
    }
}
