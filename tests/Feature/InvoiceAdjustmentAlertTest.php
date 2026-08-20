<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\User;
use App\Services\BtcRate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tests\Traits\CreatesTestInvoices;

/**
 * #158: client over/underpayment alerts judge the client's payments only.
 * An issuer ledger adjustment may clear an alert condition but never
 * creates one and never generates client mail.
 */
class InvoiceAdjustmentAlertTest extends TestCase
{
    use DatabaseTransactions, CreatesTestInvoices;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::put(BtcRate::CACHE_KEY, [
            'rate_usd' => 50_000.00,
            'as_of' => Carbon::now(),
            'source' => 'test',
        ], BtcRate::TTL);
    }

    protected function tearDown(): void
    {
        Cache::forget(BtcRate::CACHE_KEY);
        parent::tearDown();
    }

    public function test_credit_adjustment_over_threshold_queues_no_overpay_alert(): void
    {
        Queue::fake();

        [$invoice, $owner] = $this->makeSentInvoice();
        $this->addConfirmedPayment($invoice, 'tx-full', 1.0);

        $this->actingAs($owner)
            ->post(route('invoices.payments.adjustments.store', $invoice), [
                'amount_usd' => 100, // 20% above the $500 total
                'direction' => 'increase',
                'note' => 'Goodwill credit',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('invoice_deliveries', [
            'invoice_id' => $invoice->id,
            'type' => 'client_overpay_alert',
        ]);

        $this->assertDatabaseMissing('invoice_deliveries', [
            'invoice_id' => $invoice->id,
            'type' => 'issuer_overpay_alert',
        ]);

        $invoice = $invoice->fresh('payments');
        $this->assertFalse($invoice->requiresClientOverpayAlert());
        $this->assertNull($invoice->overpaymentPercent());
    }

    public function test_adjustment_only_credit_queues_no_underpay_alert(): void
    {
        Queue::fake();

        [$invoice, $owner] = $this->makeSentInvoice();

        $this->actingAs($owner)
            ->post(route('invoices.payments.adjustments.store', $invoice), [
                'amount_usd' => 200, // 40% of the $500 total, client never paid
                'direction' => 'increase',
                'note' => 'Partial credit',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('invoice_deliveries', [
            'invoice_id' => $invoice->id,
            'type' => 'client_underpay_alert',
        ]);

        $this->assertDatabaseMissing('invoice_deliveries', [
            'invoice_id' => $invoice->id,
            'type' => 'issuer_underpay_alert',
        ]);
    }

    public function test_reopen_adjustment_queues_no_underpay_alert(): void
    {
        Queue::fake();

        [$invoice, $owner] = $this->makeSentInvoice();
        $this->addConfirmedPayment($invoice, 'tx-full-reopen', 1.0);

        $this->actingAs($owner)
            ->post(route('invoices.payments.adjustments.store', $invoice), [
                'amount_usd' => 200, // reopen 40% of a fully paid invoice
                'direction' => 'decrease',
                'note' => 'Scope dispute',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('invoice_deliveries', [
            'invoice_id' => $invoice->id,
            'type' => 'client_underpay_alert',
        ]);
    }

    public function test_issuer_credit_settling_the_balance_queues_no_underpay_alert(): void
    {
        Queue::fake();

        [$invoice, $owner] = $this->makeSentInvoice();
        $this->addConfirmedPayment($invoice, 'tx-half', 0.5);

        $this->actingAs($owner)
            ->post(route('invoices.payments.adjustments.store', $invoice), [
                'amount_usd' => 250, // settles the remaining half
                'direction' => 'increase',
                'note' => 'Paid by wire',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('invoice_deliveries', [
            'invoice_id' => $invoice->id,
            'type' => 'client_underpay_alert',
        ]);

        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_genuine_client_overpay_still_alerts_and_percent_ignores_adjustments(): void
    {
        Queue::fake();

        [$invoice, $owner] = $this->makeSentInvoice();
        $this->addConfirmedPayment($invoice, 'tx-overpay', 1.2);

        $this->actingAs($owner)
            ->post(route('invoices.payments.adjustments.store', $invoice), [
                'amount_usd' => 50,
                'direction' => 'increase',
                'note' => 'Extra credit on top of a real overpay',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoice_deliveries', [
            'invoice_id' => $invoice->id,
            'type' => 'client_overpay_alert',
        ]);

        // The reported percent describes the client's payment (20% over),
        // not the adjusted ledger total (30% over).
        $this->assertEqualsWithDelta(20.0, $invoice->fresh('payments')->overpaymentPercent(), 0.1);
    }

    public function test_adjustment_form_carries_overage_confirmation(): void
    {
        [$invoice, $owner] = $this->makeSentInvoice();
        $this->addConfirmedPayment($invoice, 'tx-confirm-ui', 1.0);

        $this->actingAs($owner)
            ->get(route('invoices.show', $invoice))
            ->assertSee('confirm-adjustment-overage')
            ->assertSee('over the invoice total');
    }

    /**
     * @return array{0: Invoice, 1: User}
     */
    private function makeSentInvoice(): array
    {
        $owner = User::factory()->create();
        $invoice = $this->makeInvoice($owner, ['status' => 'sent']);

        return [$invoice, $owner];
    }

    private function addConfirmedPayment(Invoice $invoice, string $txid, float $fractionOfTotal): void
    {
        $sats = (int) round($invoice->amount_btc * Invoice::SATS_PER_BTC * $fractionOfTotal);

        InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'txid' => $txid,
            'sats_received' => $sats,
            'detected_at' => Carbon::now(),
            'confirmed_at' => Carbon::now(),
            'usd_rate' => (float) $invoice->btc_rate,
            'fiat_amount' => round((float) $invoice->amount_usd * $fractionOfTotal, 2),
        ]);

        $invoice->refresh()->refreshPaymentState();
    }
}
