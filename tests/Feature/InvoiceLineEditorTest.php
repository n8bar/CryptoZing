<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WalletSetting;
use App\Services\HdWallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;
use Tests\Traits\CreatesTestInvoices;

class InvoiceLineEditorTest extends TestCase
{
    use DatabaseTransactions;
    use CreatesTestInvoices;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('wallet.default_network', 'testnet');
        config()->set('blockchain.unsupported_wallet_detection.proactive_address_scan_count', 0);
        config()->set('blockchain.unsupported_wallet_detection.proactive_address_scan_cap', 0);
        $this->mock(HdWallet::class, fn ($mock) => $mock->shouldReceive('deriveAddress')->andReturn('tb1qtestaddress00000000000000000000000'));
    }

    private function ownerWithWallet(): array
    {
        $owner = User::factory()->create();
        WalletSetting::create(['user_id' => $owner->id, 'network' => 'testnet', 'bip84_xpub' => 'vpub' . str_repeat('a', 20), 'onboarded_at' => now()]);
        $client = Client::create(['user_id' => $owner->id, 'name' => 'Acme', 'email' => 'billing@acme.test']);

        return [$owner, $client];
    }

    private function payload(Client $client, array $lines, array $extra = []): array
    {
        return array_merge([
            'client_id' => $client->id,
            'number' => 'INV-LINES',
            'description' => 'Job',
            'btc_rate' => 50_000,
            'invoice_date' => '2026-10-02',
            'lines' => $lines,
        ], $extra);
    }

    public function test_store_builds_the_invoice_from_its_lines(): void
    {
        [$owner, $client] = $this->ownerWithWallet();

        $this->actingAs($owner)->post(route('invoices.store'), $this->payload($client, [
            ['description' => 'Design', 'quantity' => 2, 'rate_usd' => 100],
            ['description' => 'Hosting', 'quantity' => 1, 'rate_usd' => 50],
        ]))->assertRedirect(route('invoices.index'));

        $invoice = $owner->invoices()->latest('id')->first();
        $this->assertSame('250.00', $invoice->amount_usd);
        $this->assertSame('0.00500000', $invoice->amount_btc);
        $this->assertSame(['Design', 'Hosting'], $invoice->lines->pluck('description')->all());
    }

    public function test_store_maps_a_percentage_line_to_the_lines_it_picks(): void
    {
        [$owner, $client] = $this->ownerWithWallet();

        $this->actingAs($owner)->post(route('invoices.store'), $this->payload($client, [
            ['description' => 'Parts', 'quantity' => 1, 'rate_usd' => 100],
            ['description' => 'Labor', 'quantity' => 1, 'rate_usd' => 200],
            ['description' => 'Tax', 'quantity' => 1, 'rate_usd' => 10, 'is_percentage' => 1, 'applies_to' => [0]],
        ]))->assertSessionHasNoErrors();

        $invoice = $owner->invoices()->latest('id')->first();
        $tax = $invoice->lines->last();
        $this->assertTrue($tax->is_percentage);
        $this->assertSame([$invoice->lines->first()->id], $tax->applies_to);
        $this->assertSame('310.00', $invoice->amount_usd);
    }

    public function test_store_requires_at_least_one_line(): void
    {
        [$owner, $client] = $this->ownerWithWallet();

        $this->actingAs($owner)->from(route('invoices.create'))
            ->post(route('invoices.store'), $this->payload($client, []))
            ->assertRedirect(route('invoices.create'))
            ->assertSessionHasErrors(['lines']);
    }

    public function test_store_rejects_a_percentage_line_that_picks_nothing_or_itself(): void
    {
        [$owner, $client] = $this->ownerWithWallet();

        $this->actingAs($owner)->from(route('invoices.create'))
            ->post(route('invoices.store'), $this->payload($client, [
                ['description' => 'Parts', 'quantity' => 1, 'rate_usd' => 100],
                ['description' => 'Tax', 'quantity' => 1, 'rate_usd' => 10, 'is_percentage' => 1],
                ['description' => 'Fee', 'quantity' => 1, 'rate_usd' => 5, 'is_percentage' => 1, 'applies_to' => [2]],
            ]))
            ->assertSessionHasErrors(['lines.1.applies_to', 'lines.2.applies_to']);
    }

    public function test_update_replaces_the_lines_and_recalculates(): void
    {
        $invoice = $this->makeInvoice();
        $client = $invoice->client;

        $this->actingAs($invoice->user)->put(route('invoices.update', $invoice), $this->payload($client, [
            ['description' => 'Rework', 'quantity' => 3, 'rate_usd' => 40],
        ], ['number' => $invoice->number, 'status' => 'draft', 'btc_rate' => 40_000]))
            ->assertRedirect(route('invoices.show', $invoice));

        $invoice->refresh();
        $this->assertSame(['Rework'], $invoice->lines->pluck('description')->all());
        $this->assertSame('120.00', $invoice->amount_usd);
        $this->assertSame('0.00300000', $invoice->amount_btc);
    }

    public function test_update_keeps_lines_while_the_public_link_is_enabled(): void
    {
        $invoice = $this->makeInvoice(null, ['public_enabled' => true, 'public_token' => str_repeat('t', 40)]);

        $this->actingAs($invoice->user)->from(route('invoices.edit', $invoice))
            ->put(route('invoices.update', $invoice), $this->payload($invoice->client, [
                ['description' => 'Changed', 'quantity' => 1, 'rate_usd' => 999],
            ], ['number' => $invoice->number, 'status' => 'draft']))
            ->assertRedirect(route('invoices.edit', $invoice))
            ->assertSessionHas('status', 'Disable the public link to edit invoice details.');

        $this->assertSame('500.00', $invoice->fresh()->amount_usd);
    }
}
