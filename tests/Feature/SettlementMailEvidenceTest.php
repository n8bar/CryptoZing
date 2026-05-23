<?php

namespace Tests\Feature;

use App\Mail\InvoiceIssuerPaidNoticeMail;
use App\Mail\InvoicePaidReceiptMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SettlementMailEvidenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_receipt_renders_all_confirmed_txids_for_multi_payment_invoice(): void
    {
        [$invoice, $delivery, $payments] = $this->makeMultiPaymentPaidInvoice();

        $html = (new InvoicePaidReceiptMail($invoice->fresh(['client', 'user', 'payments']), $delivery))->render();

        foreach ($payments as $payment) {
            $this->assertStringContainsString($payment->txid, $html, "Receipt missing txid {$payment->txid}");
            $this->assertStringContainsString('$' . number_format((float) $payment->fiat_amount, 2), $html);
            $this->assertStringContainsString(number_format($payment->sats_received) . ' sats', $html);
        }

        $this->assertStringContainsString('across 3 on-chain payments', $html);
    }

    public function test_issuer_paid_notice_renders_all_confirmed_txids_for_multi_payment_invoice(): void
    {
        [$invoice, $delivery, $payments] = $this->makeMultiPaymentPaidInvoice();

        $html = (new InvoiceIssuerPaidNoticeMail($invoice->fresh(['client', 'user', 'payments']), $delivery))->render();

        $this->assertStringContainsString('On-chain settlement:', $html);
        foreach ($payments as $payment) {
            $this->assertStringContainsString($payment->txid, $html, "Paid notice missing txid {$payment->txid}");
            $this->assertStringContainsString('$' . number_format((float) $payment->fiat_amount, 2), $html);
            $this->assertStringContainsString(number_format($payment->sats_received) . ' sats', $html);
        }
    }

    public function test_single_payment_paid_invoice_still_renders_one_txid_cleanly(): void
    {
        [$invoice, $delivery, $payment] = $this->makeSinglePaymentPaidInvoice();

        $receiptHtml = (new InvoicePaidReceiptMail($invoice->fresh(['client', 'user', 'payments']), $delivery))->render();
        $issuerHtml = (new InvoiceIssuerPaidNoticeMail($invoice->fresh(['client', 'user', 'payments']), $delivery))->render();

        foreach ([$receiptHtml, $issuerHtml] as $html) {
            $this->assertStringContainsString($payment->txid, $html);
        }

        $this->assertStringNotContainsString('across 1 on-chain payments', $receiptHtml);
    }

    private function makeMultiPaymentPaidInvoice(): array
    {
        $owner = User::factory()->create([
            'email' => 'multi-pay-owner@example.com',
            'name' => 'Multi Owner',
        ]);

        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Multi Pay Client',
            'email' => 'multi-pay-client@example.com',
        ]);

        $invoice = Invoice::create([
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'number' => 'INV-MULTI-PAY',
            'amount_usd' => 90,
            'btc_rate' => 50_000,
            'amount_btc' => 0.0018,
            'payment_address' => 'tb1qq0multipayinvoice',
            'status' => 'paid',
            'invoice_date' => Carbon::now()->toDateString(),
        ]);
        $invoice->enablePublicShare();

        $payments = collect();
        foreach (range(1, 3) as $i) {
            $payments->push(InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'accounting_invoice_id' => $invoice->id,
                'txid' => str_pad('mp' . $i, 64, '0', STR_PAD_RIGHT),
                'sats_received' => 60_000,
                'detected_at' => Carbon::now()->subMinutes(10 - $i),
                'confirmed_at' => Carbon::now()->subMinutes(9 - $i),
                'usd_rate' => 50_000,
                'fiat_amount' => 30.00,
            ]));
        }

        $delivery = InvoiceDelivery::create([
            'invoice_id' => $invoice->id,
            'user_id' => $owner->id,
            'type' => 'receipt',
            'status' => 'sent',
            'recipient' => $client->email,
            'dispatched_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
        ]);

        return [$invoice, $delivery, $payments];
    }

    private function makeSinglePaymentPaidInvoice(): array
    {
        $owner = User::factory()->create([
            'email' => 'single-pay-owner@example.com',
            'name' => 'Single Owner',
        ]);

        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Single Pay Client',
            'email' => 'single-pay-client@example.com',
        ]);

        $invoice = Invoice::create([
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'number' => 'INV-SINGLE-PAY',
            'amount_usd' => 50,
            'btc_rate' => 50_000,
            'amount_btc' => 0.001,
            'payment_address' => 'tb1qq0singlepayinvoice',
            'status' => 'paid',
            'invoice_date' => Carbon::now()->toDateString(),
        ]);
        $invoice->enablePublicShare();

        $payment = InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'accounting_invoice_id' => $invoice->id,
            'txid' => str_pad('sp1', 64, '0', STR_PAD_RIGHT),
            'sats_received' => 100_000,
            'detected_at' => Carbon::now()->subMinute(),
            'confirmed_at' => Carbon::now(),
            'usd_rate' => 50_000,
            'fiat_amount' => 50.00,
        ]);

        $delivery = InvoiceDelivery::create([
            'invoice_id' => $invoice->id,
            'user_id' => $owner->id,
            'type' => 'receipt',
            'status' => 'sent',
            'recipient' => $client->email,
            'dispatched_at' => Carbon::now(),
            'sent_at' => Carbon::now(),
        ]);

        return [$invoice, $delivery, $payment];
    }
}
