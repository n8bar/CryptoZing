<?php

namespace Tests\Feature\Donations;

use App\Models\Donation;
use App\Services\BtcRate;
use App\Services\HdWallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class DonationPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'donations.xpub' => 'tpubDonationTest',
            'donations.max_unpaid_addresses' => 5,
            'wallet.default_network' => 'testnet4',
        ]);

        Cache::put(BtcRate::CACHE_KEY, [
            'rate_usd' => 30000.00,
            'as_of' => now(),
            'source' => 'cache',
        ], BtcRate::TTL);
    }

    private function makeDonation(array $overrides = []): Donation
    {
        return Donation::query()->create(array_merge([
            'derivation_index' => 7,
            'address' => 'tb1qdonatepaid0',
            'network' => 'testnet4',
            'usd_amount_requested' => 25.00,
            'status' => 'pending',
            'allocated_at' => now(),
        ], $overrides));
    }

    public function test_donate_page_renders_for_guests_with_disclaimer_and_no_address(): void
    {
        $this->get('/donate')
            ->assertOk()
            ->assertSee('CryptoZing LLC')
            ->assertSee('not tax-deductible')
            ->assertSee('Donate $5');

        $this->assertSame(0, Donation::count());
    }

    public function test_preset_buttons_allocate_their_labeled_amount_despite_an_empty_custom_field(): void
    {
        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->once()
                ->andReturn('tb1qpreset0');
        });

        $this->post('/donate', ['preset_amount' => 5, 'amount' => ''])
            ->assertRedirect(route('donate.show'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('donations', [
            'address' => 'tb1qpreset0',
            'usd_amount_requested' => '5.00',
        ]);
    }

    public function test_full_pool_shows_a_busy_notice_instead_of_an_address(): void
    {
        config(['donations.max_unpaid_addresses' => 1]);

        $this->makeDonation();

        $this->post('/donate', ['amount' => 25])->assertRedirect(route('donate.show'));

        $this->get('/donate')
            ->assertOk()
            ->assertSee('try again')
            ->assertDontSee('tb1qdonatepaid0');

        $this->assertSame(1, Donation::count());
    }

    public function test_rate_outage_shows_a_notice_instead_of_silently_dropping_the_amount(): void
    {
        Cache::forget(BtcRate::CACHE_KEY);
        \Illuminate\Support\Facades\Http::fake([
            'api.coinbase.com/*' => \Illuminate\Support\Facades\Http::response(null, 500),
        ]);

        $donation = $this->makeDonation(['address' => 'tb1qrateless0', 'derivation_index' => 3]);

        $this->withSession(['donation_id' => $donation->id])
            ->get('/donate')
            ->assertOk()
            ->assertSee('tb1qrateless0')
            ->assertSee('rate unavailable');
    }

    public function test_donate_again_clears_the_paid_session_and_returns_to_the_picker(): void
    {
        $donation = $this->makeDonation([
            'status' => 'paid',
            'txid' => 'donation-tx-9',
            'sats_received' => 90000,
            'paid_at' => now(),
        ]);

        $this->withSession(['donation_id' => $donation->id])
            ->post('/donate/reset')
            ->assertRedirect(route('donate.show'));

        $this->get('/donate')
            ->assertOk()
            ->assertSee('Donate $5')
            ->assertDontSee('donation-tx-9');
    }

    public function test_choosing_an_amount_allocates_an_address_and_shows_bip21_qr_payload(): void
    {
        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->once()
                ->andReturn('tb1qdonatepage0');
        });

        $this->post('/donate', ['amount' => 25])->assertRedirect(route('donate.show'));

        $this->get('/donate')
            ->assertOk()
            ->assertSee('tb1qdonatepage0')
            ->assertSee('0.00083333')
            ->assertSee('bitcoin:tb1qdonatepage0?amount=0.00083333', false);
    }

    public function test_same_session_reuses_its_pending_address_across_amount_changes(): void
    {
        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->once()
                ->andReturn('tb1qdonatepage0');
        });

        $this->post('/donate', ['amount' => 25]);
        $this->post('/donate', ['amount' => 40]);

        $this->assertSame(1, Donation::count());

        $this->get('/donate')
            ->assertOk()
            ->assertSee('tb1qdonatepage0')
            ->assertSee('0.00133333');
    }

    public function test_invalid_amounts_are_rejected(): void
    {
        $this->from('/donate')->post('/donate', ['amount' => ''])
            ->assertRedirect('/donate')
            ->assertSessionHasErrors('amount');

        $this->from('/donate')->post('/donate', ['amount' => 0.25])
            ->assertRedirect('/donate')
            ->assertSessionHasErrors('amount');
    }

    public function test_paid_donation_shows_thank_you_receipt_with_payment_details(): void
    {
        $donation = $this->makeDonation([
            'status' => 'paid',
            'txid' => 'donation-tx-9',
            'sats_received' => 90000,
            'paid_at' => now(),
        ]);

        $this->withSession(['donation_id' => $donation->id])
            ->get('/donate')
            ->assertOk()
            ->assertSee('Thank you')
            ->assertSee('donation-tx-9')
            ->assertSee('tb1qdonatepaid0')
            ->assertSee('90,000')
            ->assertSee('not tax-deductible')
            ->assertSee('Save or print');
    }

    public function test_repeated_anonymous_sessions_cannot_derive_past_the_pool_cap(): void
    {
        config(['donations.max_unpaid_addresses' => 2]);

        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->twice()
                ->andReturn('tb1qcap0', 'tb1qcap1');
        });

        $this->post('/donate', ['amount' => 5]);
        $this->flushSession();
        $this->post('/donate', ['amount' => 5]);
        $this->flushSession();
        $this->post('/donate', ['amount' => 5])->assertRedirect(route('donate.show'));

        $this->assertSame(2, Donation::count());
    }

    public function test_donations_do_not_appear_on_invoice_surfaces(): void
    {
        $donation = $this->makeDonation([
            'status' => 'paid',
            'txid' => 'donation-tx-leakcheck',
            'sats_received' => 90000,
            'paid_at' => now(),
        ]);

        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee($donation->address)
            ->assertDontSee('donation-tx-leakcheck');

        $this->actingAs($user)->get(route('invoices.index'))
            ->assertOk()
            ->assertDontSee($donation->address)
            ->assertDontSee('donation-tx-leakcheck');
    }

    public function test_status_endpoint_reports_paid_state_for_the_session_donation(): void
    {
        $this->getJson('/donate/status')->assertOk()->assertJson(['paid' => false]);

        $pending = $this->makeDonation();

        $this->withSession(['donation_id' => $pending->id])
            ->getJson('/donate/status')
            ->assertOk()
            ->assertJson(['paid' => false]);

        $pending->forceFill(['status' => 'paid', 'paid_at' => now()])->save();

        $this->withSession(['donation_id' => $pending->id])
            ->getJson('/donate/status')
            ->assertOk()
            ->assertJson(['paid' => true]);
    }
}
