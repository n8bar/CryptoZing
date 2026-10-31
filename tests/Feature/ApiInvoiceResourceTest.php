<?php

namespace Tests\Feature;

use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tests\Traits\CreatesTestInvoices;

class ApiInvoiceResourceTest extends TestCase
{
    use CreatesTestInvoices;
    use DatabaseTransactions;

    private function render(Invoice $invoice): array
    {
        return (new InvoiceResource($invoice->fresh()))->toArray(Request::create('/'));
    }

    public function test_an_invoice_renders_as_the_reference_describes(): void
    {
        $invoice = $this->makeInvoice();
        $invoice->lines()->delete();
        $design = $invoice->lines()->create(['position' => 1, 'description' => 'Design', 'quantity' => 2, 'rate_usd' => 100]);
        $subtotal = $invoice->lines()->create(['position' => 2, 'description' => 'Subtotal', 'kind' => 'subtotal']);
        $invoice->lines()->create(['position' => 3, 'description' => 'Sales tax', 'kind' => 'percentage', 'rate_usd' => 8.5, 'applies_to' => [$subtotal->id]]);
        $invoice->forceFill(['btc_rate' => 86454.18, 'amount_btc' => 0.00251])->save();
        $invoice->enablePublicShare();

        $body = $this->render($invoice);

        $this->assertStringStartsWith('inv_', $body['id']);
        $this->assertSame($invoice->number, $body['number']);
        $this->assertSame(['name' => 'Acme Co', 'email' => 'billing@example.com'], $body['client']);
        $this->assertSame([
            ['description' => 'Design', 'kind' => 'item', 'quantity' => '2.0000', 'rate_usd' => '100.00', 'amount_usd' => '200.00'],
            ['description' => 'Subtotal', 'kind' => 'subtotal', 'amount_usd' => '200.00'],
            ['description' => 'Sales tax', 'kind' => 'percentage', 'rate_usd' => '8.5', 'applies_to' => [1], 'amount_usd' => '17.00'],
        ], $body['lines']);
        $this->assertSame('217.00', $body['total_usd']);
        $this->assertSame('0.00', $body['paid_usd']);
        $this->assertSame($invoice->fresh()->public_url, $body['public_url']);
        $this->assertSame($invoice->payment_address, $body['payment']['address']);
        $this->assertSame('0.00251000', $body['payment']['amount_btc']);
        $this->assertStringStartsWith('bitcoin:' . $invoice->payment_address . '?amount=0.00251', $body['payment']['uri']);
        $this->assertSame('86454.18', $body['payment']['rate_usd_per_btc']);
        $this->assertSame('none', $body['mail_state']);
        $this->assertSame('none', $body['payment_state']);
        $this->assertSame($invoice->due_date->toDateString(), $body['due_date']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d\d-\d\dT\d\d:\d\d:\d\dZ$/', $body['created_at']);
    }

    public function test_api_ids_are_unique_and_fixed(): void
    {
        $first = $this->makeInvoice();
        $second = $this->makeInvoice();

        $this->assertNotSame($first->api_id, $second->api_id);
        $id = $first->api_id;
        $first->update(['description' => 'Changed']);
        $this->assertSame($id, $first->fresh()->api_id);
    }

    public function test_mail_state_follows_the_latest_invoice_email(): void
    {
        $invoice = $this->makeInvoice();
        $send = fn (string $status) => $invoice->deliveries()->create([
            'user_id' => $invoice->user_id, 'type' => 'send', 'status' => $status, 'recipient' => 'billing@example.com',
        ]);

        $send('queued');
        $this->assertSame('queued', $this->render($invoice)['mail_state']);

        $send('sent');
        $this->assertSame('delivered', $this->render($invoice)['mail_state']);

        $send('failed');
        $this->assertSame('failed', $this->render($invoice)['mail_state']);
    }

    public function test_payment_state_tells_seen_from_confirmed(): void
    {
        $invoice = $this->makeInvoice();
        $payment = $invoice->payments()->create([
            'invoice_id' => $invoice->id, 'txid' => str_repeat('a', 64), 'vout_index' => 0,
            'sats_received' => 500_000, 'detected_at' => now(), 'usd_rate' => 50_000,
        ]);

        $body = $this->render($invoice);
        $this->assertSame('seen', $body['payment_state']);
        $this->assertSame('250.00', $body['paid_usd']);

        $payment->update(['confirmed_at' => now()]);
        $this->assertSame('confirmed', $this->render($invoice)['payment_state']);
    }
}
