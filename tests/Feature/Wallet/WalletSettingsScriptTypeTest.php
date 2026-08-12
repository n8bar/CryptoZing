<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use App\Models\WalletSetting;
use App\Services\HdWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WalletSettingsScriptTypeTest extends TestCase
{
    use RefreshDatabase;

    private const TPUB = 'tpubDCMX5n5xeyKFQ1R98FTjQ21An9e2SgN8gF5pa4DJNfQd8B5CYCqkkWXEmH4YrxRAEDzFSv25yineuGfvFAg9tWJcGakvm7Ft5e41jQZ2bHk';
    private const VPUB = 'vpub5Z9vQhCkh1Z4BtN3fRK7aN5JEq2PHttwbJpsrs2gbHE1nPjQZ5e4DEkZPizSGZNnvmjTiR2zaUjL2Pv5gSLjXUq3ud994RSDjhuyt8LHQvv';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('wallet.default_network', 'testnet');

        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')->andReturn('tb1qtestaddress00000000000000000000000');
        });
    }

    public function test_tr_descriptor_saves_bip86_and_extracts_inner_key(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => 'tr([73c5da0a/86h/1h/0h]' . self::TPUB . '/0/*)',
        ]);

        $response->assertSessionHasNoErrors();
        $wallet = $user->fresh()->walletSetting;
        $this->assertSame('bip86', $wallet->script_type);
        $this->assertSame(self::TPUB, $wallet->bip84_xpub);
    }

    public function test_bare_tpub_with_taproot_choice_saves_bip86(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::TPUB,
            'script_type' => 'bip86',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('bip86', $user->fresh()->walletSetting->script_type);
    }

    public function test_bare_tpub_defaults_to_bip84(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::TPUB,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('bip84', $user->fresh()->walletSetting->script_type);
    }

    public function test_slip132_key_with_taproot_choice_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::VPUB,
            'script_type' => 'bip86',
        ]);

        $response->assertSessionHasErrors('bip84_xpub');
        $this->assertNull($user->fresh()->walletSetting);
    }

    public function test_seed_phrase_is_rejected_with_seed_warning(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => 'abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about',
        ]);

        $response->assertSessionHasErrors([
            'bip84_xpub' => 'That looks like a private key or seed phrase. Never paste those here — only the public account key or receive descriptor.',
        ]);
        $this->assertNull($user->fresh()->walletSetting);
    }

    public function test_malformed_descriptor_is_rejected_with_descriptor_error(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => 'tr(' . self::TPUB,
        ]);

        $response->assertSessionHasErrors([
            'bip84_xpub' => 'We could not read that descriptor. Copy the receive descriptor exactly as your wallet exports it.',
        ]);
        $this->assertNull($user->fresh()->walletSetting);
    }

    public function test_preview_endpoint_passes_script_type_from_descriptor(): void
    {
        $user = User::factory()->create();

        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->once()
                ->with(self::TPUB, 0, 'testnet', 'bip86')
                ->andReturn('tb1ptestaddress0000000000000000000000000000000000000000000000');
        });

        $response = $this->actingAs($user)->post(route('wallet.settings.validate'), [
            'bip84_xpub' => 'tr(' . self::TPUB . '/0/*)',
        ]);

        $response->assertOk();
        $response->assertJson(['address' => 'tb1ptestaddress0000000000000000000000000000000000000000000000']);
    }

    public function test_settings_page_renders_script_type_chooser(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('wallet.settings.edit'));

        $response->assertOk();
        $response->assertSee('name="script_type"', false);
        $response->assertSee('Taproot — addresses start with');
    }

    public function test_script_type_change_alone_requires_step_up(): void
    {
        $user = User::factory()->create();
        WalletSetting::create([
            'user_id' => $user->id,
            'network' => 'testnet',
            'bip84_xpub' => self::TPUB,
            'script_type' => 'bip84',
            'onboarded_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('wallet.settings.update'), [
            'bip84_xpub' => self::TPUB,
            'script_type' => 'bip86',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertSame('bip84', $user->fresh()->walletSetting->script_type);
    }
}
