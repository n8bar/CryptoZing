<?php

namespace Tests\Feature;

use App\Jobs\DeliverInvoiceMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\User;
use App\Services\InvoiceDeliveryService;
use App\Services\MailAlias;
use App\Services\MailgunEventLookup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DeliveryRetryTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array{0: User, 1: Invoice, 2: InvoiceDelivery}
     */
    private function makeQueuedDelivery(string $type = 'send', string $status = 'queued'): array
    {
        $owner = User::factory()->create();
        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Retry Client',
            'email' => 'retry-client@nospam.site',
        ]);

        $invoice = Invoice::create([
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'number' => 'INV-RETRY-' . substr(md5(uniqid('', true)), 0, 6),
            'amount_usd' => 120,
            'btc_rate' => 30_000,
            'amount_btc' => 0.004,
            'payment_address' => 'tb1qq0exampleretry',
            'status' => 'sent',
            'invoice_date' => now()->toDateString(),
        ]);
        $invoice->enablePublicShare();

        $delivery = InvoiceDelivery::create([
            'invoice_id' => $invoice->id,
            'user_id' => $owner->id,
            'type' => $type,
            'status' => $status,
            'recipient' => $client->email,
            'dispatched_at' => now(),
        ]);

        return [$owner, $invoice, $delivery];
    }

    private function runJob(InvoiceDelivery $delivery): void
    {
        (new DeliverInvoiceMail($delivery))
            ->handle(app(MailAlias::class), app(InvoiceDeliveryService::class));
    }

    public function test_transient_failure_retries_then_succeeds_with_a_single_send(): void
    {
        [, $invoice, $delivery] = $this->makeQueuedDelivery();

        $sendCalls = 0;
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andReturnUsing(function () use (&$sendCalls) {
            $sendCalls++;
            if ($sendCalls === 1) {
                throw new \RuntimeException('transient mailer error');
            }

            return null;
        });

        // Attempt 1: send fails → delivery released back to `queued`, exception re-raised.
        try {
            $this->runJob($delivery);
            $this->fail('Expected the first attempt to throw.');
        } catch (\RuntimeException $e) {
            // expected
        }
        $this->assertSame('queued', $delivery->fresh()->status);

        // Attempt 2: send succeeds → delivery is `sent`.
        $this->runJob($delivery);

        $this->assertSame('sent', $delivery->fresh()->status);
        $this->assertSame(2, $sendCalls); // one failed attempt + one successful retry
        $this->assertSame(
            1,
            InvoiceDelivery::where('invoice_id', $invoice->id)->where('type', 'send')->count(),
            'Retry must not create a duplicate delivery row.'
        );
    }

    public function test_persistent_failure_marks_failed_only_after_retries_are_exhausted(): void
    {
        [, , $delivery] = $this->makeQueuedDelivery();

        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('mailer is down'));

        $job = new DeliverInvoiceMail($delivery);
        $last = null;

        // Each in-budget attempt re-raises and leaves the delivery retryable, never terminal.
        for ($i = 0; $i < 3; $i++) {
            try {
                $job->handle(app(MailAlias::class), app(InvoiceDeliveryService::class));
                $this->fail('Expected each attempt to throw.');
            } catch (\RuntimeException $e) {
                $last = $e;
            }
            $this->assertSame('queued', $delivery->fresh()->status, 'handle() must not record terminal failed.');
        }

        // Only the queue's failed() hook (fired after the budget is exhausted) records terminal failed.
        $job->failed($last);
        $this->assertSame('failed', $delivery->fresh()->status);
        $this->assertSame('mailer is down', $delivery->fresh()->error_message);
    }

    public function test_already_sent_delivery_is_not_resent_on_re_run(): void
    {
        [, $invoice, $delivery] = $this->makeQueuedDelivery();

        $sendCalls = 0;
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andReturnUsing(function () use (&$sendCalls) {
            $sendCalls++;

            return null;
        });

        $this->runJob($delivery);
        $this->assertSame('sent', $delivery->fresh()->status);

        // Re-running the same job must not send again (idempotency across retries).
        $this->runJob($delivery);

        $this->assertSame(1, $sendCalls);
        $this->assertSame(
            1,
            InvoiceDelivery::where('invoice_id', $invoice->id)->where('type', 'send')->count()
        );
    }

    public function test_stuck_sending_reconciles_to_sent_when_provider_confirms(): void
    {
        // A leftover `sending` row (crash/post-send-write failure) must reconcile,
        // never re-send. Provider confirms it went out → `sent`.
        config(['mail.default' => 'mailgun']);
        $this->mock(MailgunEventLookup::class, fn ($m) => $m->shouldReceive('wasAccepted')->andReturn(true));

        [, , $delivery] = $this->makeQueuedDelivery('send', 'sending');

        $sendCalls = 0;
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andReturnUsing(function () use (&$sendCalls) {
            $sendCalls++;

            return null;
        });

        $this->runJob($delivery);

        $this->assertSame(0, $sendCalls, 'Reconcile must not re-send.');
        $this->assertSame('sent', $delivery->fresh()->status);
    }

    public function test_stuck_sending_retries_when_provider_does_not_confirm(): void
    {
        // Provider can't confirm → throw to keep the queue retrying toward failed();
        // still never re-sends.
        config(['mail.default' => 'mailgun']);
        $this->mock(MailgunEventLookup::class, fn ($m) => $m->shouldReceive('wasAccepted')->andReturn(false));

        [, , $delivery] = $this->makeQueuedDelivery('send', 'sending');

        $sendCalls = 0;
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andReturnUsing(function () use (&$sendCalls) {
            $sendCalls++;

            return null;
        });

        try {
            $this->runJob($delivery);
            $this->fail('Expected an unconfirmed sending delivery to throw for retry.');
        } catch (\RuntimeException $e) {
            // expected
        }

        $this->assertSame(0, $sendCalls, 'Reconcile must not re-send.');
        $this->assertSame('sending', $delivery->fresh()->status);
    }

    public function test_owner_can_resend_a_failed_non_receipt_delivery(): void
    {
        Queue::fake();

        [$owner, $invoice, $delivery] = $this->makeQueuedDelivery('client_underpay_alert', 'failed');

        $response = $this->actingAs($owner)
            ->post(route('invoices.deliver.resend', ['invoice' => $invoice, 'delivery' => $delivery]));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Delivery resend queued.');

        // A fresh queued resend row is created for the same notice class + recipient.
        $resend = InvoiceDelivery::where('invoice_id', $invoice->id)
            ->where('type', 'client_underpay_alert')
            ->where('status', 'queued')
            ->where('id', '!=', $delivery->id)
            ->first();

        $this->assertNotNull($resend, 'A resend delivery should be queued.');
        $this->assertStringStartsWith('resend_', (string) $resend->context_key);
        $this->assertSame($delivery->recipient, $resend->recipient);

        Queue::assertPushed(DeliverInvoiceMail::class, fn ($job) => $job->delivery->is($resend));
    }
}
