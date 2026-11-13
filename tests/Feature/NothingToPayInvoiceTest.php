<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WalletSetting;
use App\Services\BtcRate;
use App\Services\HdWallet;
use App\Services\InvoicePaymentSyncService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/** Invoices at $0 or less: LINE_ITEMS.md §4. */
class NothingToPayInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    private User $owner;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Config::set('wallet.default_network', 'testnet');
        config()->set('blockchain.unsupported_wallet_detection.proactive_address_scan_count', 0);
        config()->set('blockchain.unsupported_wallet_detection.proactive_address_scan_cap', 0);
        config()->set('mail.safety.outbound_enabled', true);
        $this->mock(HdWallet::class, fn ($mock) => $mock->shouldReceive('deriveAddress')->andReturn('tb1qtestaddress00000000000000000000000'));
        Cache::put(BtcRate::CACHE_KEY, ['rate_usd' => 50_000.0, 'as_of' => now(), 'source' => 'test'], BtcRate::TTL);

        $this->owner = User::factory()->create();
        WalletSetting::create(['user_id' => $this->owner->id, 'network' => 'testnet', 'bip84_xpub' => 'vpub' . str_repeat('a', 20), 'onboarded_at' => now()]);
        $this->client = Client::create(['user_id' => $this->owner->id, 'name' => 'Acme', 'email' => 'billing@acme.test']);
    }

    private function payload(array $lines, array $extra = []): array
    {
        return array_merge([
            'client_id' => $this->client->id,
            'description' => 'Job',
            'btc_rate' => 50_000,
            'invoice_date' => '2026-11-12',
            'lines' => $lines,
        ], $extra);
    }

    private function createInApp(float $rate, float $discount = 0): Invoice
    {
        $lines = [['description' => 'Work', 'quantity' => 1, 'rate_usd' => $rate]];
        if ($discount !== 0.0) {
            $lines[] = ['description' => 'Credit', 'quantity' => 1, 'rate_usd' => $discount];
        }
        $this->actingAs($this->owner)->post(route('invoices.store'), $this->payload($lines, ['number' => 'INV-' . random_int(1000, 9999)]))->assertSessionHasNoErrors();

        return $this->owner->invoices()->latest('id')->first();
    }

    public function test_a_zero_or_negative_invoice_gets_no_address(): void
    {
        $this->assertNull($this->createInApp(100, -100)->payment_address);
        $this->assertNull($this->createInApp(100, -150)->payment_address);
        $this->assertNotNull($this->createInApp(100)->payment_address);
    }

    public function test_a_zero_draft_reads_paid_once_sent(): void
    {
        $invoice = $this->createInApp(0);
        $this->assertSame('draft', $invoice->status);

        $this->actingAs($this->owner)->patch(route('invoices.set-status', [$invoice, 'sent']));

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);
    }

    public function test_a_negative_draft_cannot_be_sent(): void
    {
        $invoice = $this->createInApp(100, -150);

        $this->actingAs($this->owner)->patch(route('invoices.set-status', [$invoice, 'sent']))->assertSessionHas('error', Invoice::SEND_NEEDS_ZERO_OR_MORE);
        $invoice->enablePublicShare();
        $this->actingAs($this->owner)->post(route('invoices.deliver', $invoice))->assertSessionHas('status', Invoice::SEND_NEEDS_ZERO_OR_MORE);

        $this->assertSame('draft', $invoice->fresh()->status);
        $this->assertSame(0, $invoice->deliveries()->count());
    }

    public function test_editing_across_zero_gives_and_takes_back_the_address(): void
    {
        $invoice = $this->createInApp(0);
        $edit = fn (float $rate) => $this->actingAs($this->owner)->put(route('invoices.update', $invoice), $this->payload(
            [['description' => 'Work', 'quantity' => 1, 'rate_usd' => $rate]],
            ['number' => $invoice->number],
        ))->assertSessionHasNoErrors();

        $edit(100);
        $this->assertNotNull($invoice->fresh()->payment_address);

        $edit(0);
        $this->assertNull($invoice->fresh()->payment_address);
    }

    public function test_only_a_draft_can_total_below_zero(): void
    {
        $invoice = $this->createInApp(100);

        $this->actingAs($this->owner)->put(route('invoices.update', $invoice), $this->payload(
            [['description' => 'Work', 'quantity' => 1, 'rate_usd' => -5]],
            ['number' => $invoice->number, 'status' => 'sent'],
        ))->assertSessionHasErrors('lines');
    }

    public function test_nothing_to_pay_is_never_watched(): void
    {
        $invoice = $this->createInApp(0);
        $invoice->forceFill(['payment_address' => 'tb1qleftover0000000000000000000000000'])->save();

        $this->mock(InvoicePaymentSyncService::class, fn ($mock) => $mock->shouldNotReceive('syncInvoice'));

        $this->artisan('wallet:watch-payments', ['--invoice' => $invoice->id])->assertSuccessful();
    }

    public function test_print_crosses_out_the_qr_code(): void
    {
        $zero = $this->createInApp(0);
        $this->actingAs($this->owner)->get(route('invoices.print', $zero))->assertOk()->assertSee('Nothing to pay')->assertDontSee('Send one payment');

        $owed = $this->createInApp(100);
        $this->actingAs($this->owner)->get(route('invoices.print', $owed))->assertOk()->assertDontSee('Nothing to pay');
    }

    public function test_the_api_shows_no_payment_and_refuses_sending_below_zero(): void
    {
        [, $plain] = ApiKey::issue($this->owner, 'Shop');
        $api = fn (string $key, array $lines, bool $send = false) => $this->withToken($plain)->withHeader('Idempotency-Key', $key)->postJson('/api/v1/invoices', [
            'client' => ['name' => 'Acme', 'email' => 'billing@acme.test'],
            'lines' => $lines,
            'send' => $send,
        ]);
        $credit = [['description' => 'Work', 'quantity' => 1, 'rate_usd' => '100'], ['description' => 'Credit', 'quantity' => 1, 'rate_usd' => '-150']];
        $count = fn () => Invoice::where('user_id', $this->owner->id)->count();

        $api('neg-send', $credit, true)->assertStatus(422)->assertJsonPath('error.field', 'lines');
        $this->assertSame(0, $count());

        $id = $api('neg-draft', $credit)->assertStatus(201)->assertJsonPath('payment', null)->json('id');
        $this->withToken($plain)->postJson("/api/v1/invoices/{$id}/send")->assertStatus(422)->assertJsonPath('error.field', 'lines');

        $api('zero', [['description' => 'Warranty visit', 'quantity' => 1, 'rate_usd' => '0']], true)
            ->assertStatus(201)
            ->assertJsonPath('payment', null)
            ->assertJsonPath('status', 'paid')
            ->assertJsonPath('payment_state', 'none');
    }
}
