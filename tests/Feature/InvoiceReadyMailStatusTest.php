<?php

namespace Tests\Feature;

use App\Mail\InvoiceReadyMail;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The invoice email states the invoice's real status, not a fixed word (#185).
 */
class InvoiceReadyMailStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_invoice_email_reports_the_invoice_status(): void
    {
        $owner = User::factory()->create();
        $client = Client::create(['user_id' => $owner->id, 'name' => 'Status Client', 'email' => 'status@example.com']);

        foreach ([['paid', 'Paid'], ['partial', 'Partially paid'], ['sent', 'Open'], ['void', 'Void']] as [$status, $label]) {
            $invoice = Invoice::create([
                'user_id' => $owner->id,
                'client_id' => $client->id,
                'number' => 'INV-STATUS-'.strtoupper($status),
                'amount_usd' => 10,
                'btc_rate' => 40_000,
                'amount_btc' => 0.00025,
                'payment_address' => 'tb1qq0example'.$status,
                'status' => $status,
                'invoice_date' => now()->toDateString(),
            ]);
            $invoice->enablePublicShare();
            $delivery = InvoiceDelivery::create([
                'invoice_id' => $invoice->id,
                'user_id' => $owner->id,
                'type' => 'send',
                'status' => 'queued',
                'recipient' => $client->email,
                'dispatched_at' => now(),
            ]);

            $html = (new InvoiceReadyMail($invoice->fresh(), $delivery))->render();

            $this->assertStringContainsString('Status:</strong> '.$label, $html, "status {$status}");
        }
    }
}
