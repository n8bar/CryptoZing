<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use App\Models\WalletSetting;
use App\Services\BtcRate;
use App\Services\HdWallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApiInvoiceEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    private User $issuer;
    private string $plain;

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

        $this->issuer = User::factory()->create();
        WalletSetting::create(['user_id' => $this->issuer->id, 'network' => 'testnet', 'bip84_xpub' => 'vpub' . str_repeat('a', 20), 'onboarded_at' => now()]);
        [, $this->plain] = ApiKey::issue($this->issuer, 'Shop');
    }

    private function body(array $extra = []): array
    {
        return array_merge([
            'client' => ['name' => 'Acme Co', 'email' => 'billing@acme.test'],
            'lines'  => [
                ['description' => 'Design', 'quantity' => 2, 'rate_usd' => '100.00'],
                ['description' => 'Subtotal', 'kind' => 'subtotal'],
                ['description' => 'Sales tax', 'kind' => 'percentage', 'rate_usd' => '8.5', 'applies_to' => [1]],
            ],
            'due_date' => '2026-11-01',
        ], $extra);
    }

    private function create(array $body, string $key = 'order-1', ?string $plain = null)
    {
        return $this->withToken($plain ?? $this->plain)->withHeader('Idempotency-Key', $key)->postJson('/api/v1/invoices', $body);
    }

    public function test_create_builds_a_draft_with_cryptozings_rate(): void
    {
        $response = $this->create($this->body(['btc_rate' => 1]))->assertStatus(201);

        $response->assertJsonPath('total_usd', '217.00')
            ->assertJsonPath('lines.2.applies_to', [1])
            ->assertJsonPath('lines.2.amount_usd', '17.00')
            ->assertJsonPath('payment.rate_usd_per_btc', '50000.00')
            ->assertJsonPath('payment.amount_btc', '0.00434000')
            ->assertJsonPath('payment.address', 'tb1qtestaddress00000000000000000000000')
            ->assertJsonPath('status', 'draft')
            ->assertJsonPath('public_url', null)
            ->assertJsonPath('mail_state', 'none')
            ->assertJsonPath('due_date', '2026-11-01');

        $this->assertSame(1, Client::where('user_id', $this->issuer->id)->where('email', 'billing@acme.test')->count());
    }

    public function test_a_repeated_key_returns_the_first_invoice_and_a_changed_body_conflicts(): void
    {
        $id = $this->create($this->body())->assertStatus(201)->json('id');

        $this->create($this->body())->assertOk()->assertJsonPath('id', $id);
        $this->assertSame(1, Invoice::where('user_id', $this->issuer->id)->count());

        $this->create($this->body(['due_date' => '2026-12-01']))->assertStatus(409)->assertJsonPath('error.code', 'conflict');
    }

    public function test_create_needs_a_key_and_valid_lines(): void
    {
        $this->withToken($this->plain)->postJson('/api/v1/invoices', $this->body())
            ->assertStatus(422)->assertJsonPath('error.field', 'Idempotency-Key');

        $this->create($this->body(['lines' => [['description' => 'Design', 'quantity' => 1]]]))
            ->assertStatus(422)->assertJsonPath('error.code', 'validation')->assertJsonPath('error.field', 'lines.0.rate_usd');
    }

    public function test_create_refuses_an_issuer_without_a_wallet(): void
    {
        [, $plain] = ApiKey::issue(User::factory()->create(), 'No wallet');

        $this->create($this->body(), 'order-x', $plain)->assertStatus(422)->assertJsonPath('error.field', 'wallet');
    }

    public function test_create_reuses_the_client_with_that_email(): void
    {
        $client = Client::create(['user_id' => $this->issuer->id, 'name' => 'Acme', 'email' => 'billing@acme.test']);

        $id = $this->create($this->body())->json('id');

        $this->assertSame($client->id, Invoice::where('api_id', $id)->value('client_id'));
    }

    public function test_create_with_send_turns_on_the_link_and_queues_the_email(): void
    {
        $this->create($this->body(['send' => true]))->assertStatus(201)
            ->assertJsonPath('status', 'sent')
            ->assertJsonPath('mail_state', 'queued')
            ->assertJson(fn ($json) => $json->whereType('public_url', 'string')->etc());
    }

    public function test_read_returns_the_invoice_and_another_issuer_gets_not_found(): void
    {
        $id = $this->create($this->body())->json('id');

        $this->withToken($this->plain)->getJson("/api/v1/invoices/{$id}")->assertOk()->assertJsonPath('id', $id);

        [, $other] = ApiKey::issue(User::factory()->create(), 'Other');
        $this->withToken($other)->getJson("/api/v1/invoices/{$id}")->assertStatus(404)->assertJsonPath('error.code', 'not_found');
        $this->withToken($other)->deleteJson("/api/v1/invoices/{$id}")->assertStatus(404);
    }

    public function test_a_draft_can_be_edited_until_the_public_link_is_on(): void
    {
        $id = $this->create($this->body())->json('id');

        $this->withToken($this->plain)->patchJson("/api/v1/invoices/{$id}", [
            'lines' => [['description' => 'Design', 'quantity' => 1, 'rate_usd' => '50.00']],
            'due_date' => '2026-11-15',
        ])->assertOk()
            ->assertJsonPath('total_usd', '50.00')
            ->assertJsonPath('payment.amount_btc', '0.00100000')
            ->assertJsonPath('due_date', '2026-11-15');

        $this->withToken($this->plain)->postJson("/api/v1/invoices/{$id}/send")->assertStatus(202)->assertJsonPath('mail_state', 'queued');

        $this->withToken($this->plain)->patchJson("/api/v1/invoices/{$id}", ['due_date' => '2026-12-01'])
            ->assertStatus(409)->assertJsonPath('error.code', 'not_draft');
    }

    public function test_delete_keeps_the_record(): void
    {
        $id = $this->create($this->body())->json('id');

        $this->withToken($this->plain)->deleteJson("/api/v1/invoices/{$id}")->assertNoContent();

        $this->assertSoftDeleted('invoices', ['api_id' => $id]);
        $this->withToken($this->plain)->getJson("/api/v1/invoices/{$id}")->assertStatus(404);
    }
}
