<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WalletSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCreateNewClientTest extends TestCase
{
    use RefreshDatabase;

    private function createWalletSetting(User $owner): WalletSetting
    {
        return WalletSetting::create([
            'user_id' => $owner->id,
            'network' => 'testnet',
            'bip84_xpub' => 'vpub' . str_repeat('a', 20),
            'onboarded_at' => now(),
        ]);
    }

    public function test_json_client_store_returns_created_client(): void
    {
        $owner = User::factory()->create();

        $response = $this
            ->actingAs($owner)
            ->postJson(route('clients.store'), [
                'name' => 'Inline Modal Co',
                'email' => 'billing@inline-modal.test',
            ]);

        $response->assertCreated();
        $response->assertJsonFragment([
            'name' => 'Inline Modal Co',
            'email' => 'billing@inline-modal.test',
        ]);

        $this->assertDatabaseHas('clients', [
            'user_id' => $owner->id,
            'name' => 'Inline Modal Co',
            'email' => 'billing@inline-modal.test',
        ]);
    }

    public function test_json_client_store_returns_validation_errors(): void
    {
        $owner = User::factory()->create();

        $response = $this
            ->actingAs($owner)
            ->postJson(route('clients.store'), [
                'name' => '',
                'email' => 'not-an-email',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'email']);

        $this->assertDatabaseCount('clients', 0);
    }

    public function test_json_client_store_requires_authentication(): void
    {
        $response = $this->postJson(route('clients.store'), [
            'name' => 'No Auth Co',
            'email' => 'billing@no-auth.test',
        ]);

        $response->assertUnauthorized();
    }

    public function test_invoice_create_offers_new_client_option_and_prompt(): void
    {
        $owner = User::factory()->create();
        $this->createWalletSetting($owner);
        Client::create([
            'user_id' => $owner->id,
            'name' => 'Acme',
            'email' => 'billing@acme.test',
        ]);

        $response = $this
            ->actingAs($owner)
            ->get(route('invoices.create'));

        $response->assertOk();
        $response->assertSee('value="__new__"', false);
        $response->assertSee('+ New client', false);
        $response->assertSee('create-client', false);
        $response->assertSee('id="new-client-name"', false);
        $response->assertSee('id="new-client-email"', false);
    }

    public function test_client_gate_still_replaces_form_when_no_clients_exist(): void
    {
        $owner = User::factory()->create();
        $this->createWalletSetting($owner);

        $response = $this
            ->actingAs($owner)
            ->get(route('invoices.create'));

        $response->assertOk();
        $response->assertSee('Create your first client', false);
        $response->assertDontSee('value="__new__"', false);
    }
}
