<?php

namespace Tests\Feature;

use App\Mail\InvoicePaymentAcknowledgmentClientMail;
use App\Mail\InvoicePaymentAcknowledgmentIssuerMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\InvoicePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

// A payment is called partial only when every detected payment, confirmed or
// not, falls short of the total beyond tolerance (#200).
class PaymentAcknowledgmentWordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_unconfirmed_payment_is_not_called_partial(): void
    {
        [$client, $issuer] = $this->renderAcknowledgments(sats: 200_000, fiat: 80.00);

        foreach ([$client, $issuer] as $html) {
            $this->assertStringNotContainsString('partial', $html);
            $this->assertStringNotContainsString('still be due', $html);
            $this->assertStringNotContainsString('remaining balance', $html);
        }
        $this->assertStringContainsString('still waiting for the network to confirm', $client);
    }

    public function test_short_payment_within_tolerance_is_not_called_partial(): void
    {
        [$client, $issuer] = $this->renderAcknowledgments(sats: 198_750, fiat: 79.50);

        $this->assertStringNotContainsString('partial', $client);
        $this->assertStringNotContainsString('remaining balance', $issuer);
    }

    public function test_short_unconfirmed_payment_is_called_partial_with_the_true_remainder(): void
    {
        [$client, $issuer] = $this->renderAcknowledgments(sats: 120_000, fiat: 48.00);

        $this->assertStringContainsString('partial payment', $client);
        $this->assertStringContainsString('$32.00', $client);
        $this->assertStringContainsString('remaining balance of $32.00', $issuer);
    }

    /** @return array{0: string, 1: string} client and issuer HTML */
    private function renderAcknowledgments(int $sats, float $fiat): array
    {
        $owner = User::factory()->create();
        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Ack Client',
            'email' => 'ack-client@nospam.site',
        ]);

        $invoice = Invoice::create([
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'number' => 'INV-ACK-WORDING',
            'amount_usd' => 80,
            'btc_rate' => 40_000,
            'amount_btc' => 0.002,
            'payment_address' => 'tb1qw508d6qejxtdg4y5r3zarvary0c5xw7kygt080',
            'status' => 'pending',
            'public_enabled' => true,
            'public_token' => 'ack-wording-proof',
            'public_expires_at' => Carbon::now()->addDay(),
            'invoice_date' => Carbon::now()->toDateString(),
            'due_date' => Carbon::now()->addWeek()->toDateString(),
        ]);

        $payment = InvoicePayment::create([
            'invoice_id' => $invoice->id,
            'accounting_invoice_id' => $invoice->id,
            'txid' => 'ack-wording-tx',
            'sats_received' => $sats,
            'detected_at' => Carbon::now(),
            'confirmed_at' => null,
            'usd_rate' => 40_000,
            'fiat_amount' => $fiat,
        ]);

        $html = [];
        foreach ([
            ['payment_acknowledgment_client', $client->email, InvoicePaymentAcknowledgmentClientMail::class],
            ['payment_acknowledgment_issuer', $owner->email, InvoicePaymentAcknowledgmentIssuerMail::class],
        ] as [$type, $recipient, $mail]) {
            $delivery = InvoiceDelivery::create([
                'invoice_id' => $invoice->id,
                'user_id' => $owner->id,
                'type' => $type,
                'context_key' => $payment->txid,
                'status' => 'sending',
                'recipient' => $recipient,
            ]);

            $html[] = (new $mail($invoice->fresh(['client', 'user']), $delivery, $payment))->render();
        }

        return $html;
    }
}
