<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LegalLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_terms_page_renders_approved_draft_without_internal_markers(): void
    {
        $response = $this->get(route('legal.terms'));

        $response->assertOk();
        $response->assertSee('Terms of Service');
        $response->assertSee('CryptoZing LLC');
        $response->assertSee('CryptoZingTerms@CyberCreek.us');
        $response->assertSee('37signals');
        $response->assertDontSee('APPROVED');
        $response->assertDontSee('WORK IN PROGRESS');
    }

    public function test_privacy_page_renders_approved_draft_without_internal_markers(): void
    {
        $response = $this->get(route('legal.privacy'));

        $response->assertOk();
        $response->assertSee('Privacy Policy');
        $response->assertSee('CryptoZingPrivacy@CyberCreek.us');
        $response->assertSee('legalmattic');
        $response->assertDontSee('APPROVED');
        $response->assertDontSee('WORK IN PROGRESS');
    }

    public function test_register_page_shows_signup_disclaimer_with_working_policy_links(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('beta software');
        $response->assertSee('low-stakes invoices');
        $response->assertSee('href="' . route('legal.terms') . '"', false);
        $response->assertSee('href="' . route('legal.privacy') . '"', false);
    }

    public function test_wallet_settings_shows_first_person_visibility_disclaimer(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('wallet.settings.edit'));

        $response->assertOk();
        $response->assertSee('full receive history and balance');
        $response->assertSee('href="' . route('legal.privacy') . '"', false);
    }

    public function test_authenticated_pages_show_footer_links_to_both_policies(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('href="' . route('legal.terms') . '"', false);
        $response->assertSee('href="' . route('legal.privacy') . '"', false);
    }

    public function test_public_invoice_page_shows_payer_disclaimer_and_privacy_only_footer(): void
    {
        $owner = User::factory()->create();
        $invoice = $this->makeInvoice($owner);
        $invoice->enablePublicShare();

        $response = $this->get(route('invoices.public-print', ['token' => $invoice->public_token]));

        $response->assertOk();
        $response->assertSee('not a party to this transaction');
        $response->assertSee('href="' . route('legal.privacy') . '"', false);
        $response->assertDontSee('href="' . route('legal.terms') . '"', false);
    }

    private function makeInvoice(User $owner): Invoice
    {
        $client = Client::create([
            'user_id' => $owner->id,
            'name' => 'Acme Co',
            'email' => 'billing@example.com',
            'notes' => null,
        ]);

        return Invoice::create([
            'user_id' => $owner->id,
            'client_id' => $client->id,
            'number' => 'INV-0001',
            'description' => 'Consulting services',
            'amount_usd' => 100,
            'btc_rate' => 50000,
            'amount_btc' => 0.002,
            'payment_address' => 'tb1qw508d6qejxtdg4y5r3zarvary0c5xw7k3l0zz',
            'status' => 'sent',
            'invoice_date' => Carbon::now()->toDateString(),
            'due_date' => Carbon::now()->addWeek()->toDateString(),
        ]);
    }
}
