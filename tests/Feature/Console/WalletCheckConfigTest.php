<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Models\UserWalletAccount;
use App\Models\WalletSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The deploy-time check: a donation key that is malformed (#149) or that
 * duplicates an onboarded invoice key (#146) has to fail here rather than at
 * the first donor.
 */
class WalletCheckConfigTest extends TestCase
{
    use RefreshDatabase;

    private const TPUB = 'tpubDCMX5n5xeyKFQ1R98FTjQ21An9e2SgN8gF5pa4DJNfQd8B5CYCqkkWXEmH4YrxRAEDzFSv25yineuGfvFAg9tWJcGakvm7Ft5e41jQZ2bHk';

    private const OTHER_TPUB = 'tpubDCBWBScQPGv4Xk3JSbhw6wYYpayMjb2eAYyArpbSqQTbLDpphHGAetB6VQgVeftLML8vDSUEWcC2xDi3qJJ3YCDChJDvqVzpgoYSuT52MhJ';

    protected function setUp(): void
    {
        parent::setUp();

        config(['wallet.default_network' => 'testnet']);
    }

    public function test_it_passes_when_no_donation_key_is_configured(): void
    {
        config(['donations.xpub' => null]);

        $this->artisan('wallet:check-config')
            ->expectsOutputToContain('/donate')
            ->assertExitCode(0);
    }

    public function test_it_passes_for_a_usable_key_with_no_onboarded_wallets(): void
    {
        config(['donations.xpub' => 'tr(' . self::TPUB . '/0/*)']);

        $this->artisan('wallet:check-config')->assertExitCode(0);
    }

    public function test_it_fails_when_the_donation_key_is_signing_material(): void
    {
        config(['donations.xpub' => 'tprvPhpunitNeverAKey']);

        $this->artisan('wallet:check-config')
            ->expectsOutputToContain('DONATION_WALLET_XPUB')
            ->assertExitCode(1);
    }

    public function test_it_fails_when_the_donation_key_is_from_the_other_network(): void
    {
        config(['donations.xpub' => 'xpub6BgBgsespWvERF3LHQu6CnqdvfEvtMcQjYrcRzx53QJjSxarj2afYWcLteoGVky7D3UKDP9QyrLprQ3VCECoY49yfdDEHGCtMMj92pReUsQ']);

        $this->artisan('wallet:check-config')->assertExitCode(1);
    }

    public function test_it_fails_when_the_donation_key_matches_an_onboarded_invoice_key(): void
    {
        $user = User::factory()->create();
        WalletSetting::create([
            'user_id' => $user->id,
            'network' => 'testnet',
            'bip84_xpub' => self::TPUB,
            'script_type' => 'bip86',
            'onboarded_at' => now(),
        ]);

        config(['donations.xpub' => self::TPUB]);

        $this->artisan('wallet:check-config')
            ->expectsOutputToContain('invoice')
            ->assertExitCode(1);
    }

    /**
     * The paste that hides itself: a bare key derives BIP84 while the invoice
     * wallet derives Taproot, so the two chains look unrelated until the
     * addresses collide.
     */
    public function test_it_fails_when_a_bare_donation_key_matches_a_descriptor_onboarded_invoice_key(): void
    {
        $user = User::factory()->create();
        WalletSetting::create([
            'user_id' => $user->id,
            'network' => 'testnet',
            'bip84_xpub' => self::TPUB,
            'script_type' => 'bip86',
            'onboarded_at' => now(),
        ]);

        config(['donations.xpub' => 'tr(' . self::TPUB . '/0/*)']);

        $this->artisan('wallet:check-config')->assertExitCode(1);
    }

    public function test_it_fails_when_the_donation_key_matches_a_secondary_wallet_account(): void
    {
        $user = User::factory()->create();
        UserWalletAccount::create([
            'user_id' => $user->id,
            'label' => 'Second wallet',
            'network' => 'testnet',
            'bip84_xpub' => self::TPUB,
            'script_type' => 'bip84',
            'active' => true,
        ]);

        config(['donations.xpub' => self::TPUB]);

        $this->artisan('wallet:check-config')->assertExitCode(1);
    }

    public function test_it_passes_when_the_donation_key_is_a_different_key(): void
    {
        $user = User::factory()->create();
        WalletSetting::create([
            'user_id' => $user->id,
            'network' => 'testnet',
            'bip84_xpub' => self::TPUB,
            'script_type' => 'bip86',
            'onboarded_at' => now(),
        ]);

        config(['donations.xpub' => self::OTHER_TPUB]);

        $this->artisan('wallet:check-config')->assertExitCode(0);
    }
}
