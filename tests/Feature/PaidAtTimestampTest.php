<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PaidAtTimestampTest extends TestCase
{
    use DatabaseTransactions;

    private function makeInvoice(float $amountUsd = 100): Invoice
    {
        $owner = User::factory()->create();
        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'PaidAt Client',
            'email' => 'paidat-client@nospam.site',
        ]);

        return Invoice::create([
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'number' => 'INV-PAIDAT-' . substr(md5(uniqid('', true)), 0, 6),
            'amount_usd' => $amountUsd,
            'btc_rate' => 50_000,
            'amount_btc' => $amountUsd / 50_000,
            'payment_address' => 'tb1qpaidatexample',
            'status' => 'sent',
            'invoice_date' => now()->toDateString(),
        ]);
    }

    public function test_paid_at_is_the_settling_last_confirmation_on_a_multi_payment_invoice(): void
    {
        $invoice = $this->makeInvoice(100);

        $earlier = Carbon::now()->subDays(2)->startOfSecond();
        $later = Carbon::now()->subDay()->startOfSecond();

        // Two confirmed partials that together cross the $100 total, confirmed at distinct times.
        InvoicePayment::create([
            'invoice_id' => $invoice->id, 'txid' => 'tx-paidat-first', 'sats_received' => 100_000,
            'detected_at' => $earlier, 'confirmed_at' => $earlier, 'usd_rate' => 50_000, 'fiat_amount' => 50.00,
        ]);
        InvoicePayment::create([
            'invoice_id' => $invoice->id, 'txid' => 'tx-paidat-second', 'sats_received' => 100_000,
            'detected_at' => $later, 'confirmed_at' => $later, 'usd_rate' => 50_000, 'fiat_amount' => 50.00,
        ]);

        $invoice->refresh()->refreshPaymentState();

        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
        $this->assertSame(
            $later->toDateTimeString(),
            $invoice->paid_at->toDateTimeString(),
            'paid_at should be the settling (last) confirmation, not the first.'
        );
    }

    public function test_paid_at_equals_the_single_confirmation_on_a_single_payment_invoice(): void
    {
        $invoice = $this->makeInvoice(100);
        $when = Carbon::now()->subDay()->startOfSecond();

        InvoicePayment::create([
            'invoice_id' => $invoice->id, 'txid' => 'tx-paidat-only', 'sats_received' => 200_000,
            'detected_at' => $when, 'confirmed_at' => $when, 'usd_rate' => 50_000, 'fiat_amount' => 100.00,
        ]);

        $invoice->refresh()->refreshPaymentState();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame($when->toDateTimeString(), $invoice->paid_at->toDateTimeString());
    }

    public function test_paid_at_is_the_crossing_not_a_later_redundant_payment(): void
    {
        $invoice = $this->makeInvoice(100);
        $crossing = Carbon::now()->subDays(2)->startOfSecond();
        $later = Carbon::now()->subDay()->startOfSecond();

        // One payment already crosses the $100 total at $crossing.
        InvoicePayment::create([
            'invoice_id' => $invoice->id, 'txid' => 'tx-paidat-cross', 'sats_received' => 240_000,
            'detected_at' => $crossing, 'confirmed_at' => $crossing, 'usd_rate' => 50_000, 'fiat_amount' => 120.00,
        ]);
        // A later, redundant payment — NOT the crossing.
        InvoicePayment::create([
            'invoice_id' => $invoice->id, 'txid' => 'tx-paidat-extra', 'sats_received' => 100_000,
            'detected_at' => $later, 'confirmed_at' => $later, 'usd_rate' => 50_000, 'fiat_amount' => 50.00,
        ]);

        $invoice->refresh()->refreshPaymentState();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame(
            $crossing->toDateTimeString(),
            $invoice->paid_at->toDateTimeString(),
            'paid_at is the crossing confirmation, not a later redundant payment (max would be wrong).'
        );
    }

    public function test_reference_timestamp_takes_precedence_for_paid_at(): void
    {
        $invoice = $this->makeInvoice(100);
        $confirmed = Carbon::now()->subDays(2)->startOfSecond();

        InvoicePayment::create([
            'invoice_id' => $invoice->id, 'txid' => 'tx-paidat-ref', 'sats_received' => 200_000,
            'detected_at' => $confirmed, 'confirmed_at' => $confirmed, 'usd_rate' => 50_000, 'fiat_amount' => 100.00,
        ]);

        $reference = Carbon::now()->subHour()->startOfSecond();
        $invoice->refresh()->refreshPaymentState($reference);

        $this->assertSame('paid', $invoice->status);
        $this->assertSame($reference->toDateTimeString(), $invoice->paid_at->toDateTimeString());
    }
}
