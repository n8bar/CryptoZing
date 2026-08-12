<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use App\Models\UserWalletAccount;
use App\Models\WalletSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletScriptTypeTest extends TestCase
{
    use RefreshDatabase;

    private const TESTNET_TPUB = 'tpubDCMX5n5xeyKFQ1R98FTjQ21An9e2SgN8gF5pa4DJNfQd8B5CYCqkkWXEmH4YrxRAEDzFSv25yineuGfvFAg9tWJcGakvm7Ft5e41jQZ2bHk';

    public function test_wallet_setting_script_type_defaults_to_bip84(): void
    {
        $user = User::factory()->create();

        $setting = WalletSetting::create([
            'user_id' => $user->id,
            'network' => 'testnet',
            'bip84_xpub' => self::TESTNET_TPUB,
        ]);

        $this->assertSame('bip84', $setting->fresh()->script_type);
    }

    public function test_wallet_setting_persists_bip86_script_type(): void
    {
        $user = User::factory()->create();

        $setting = WalletSetting::create([
            'user_id' => $user->id,
            'network' => 'testnet',
            'bip84_xpub' => self::TESTNET_TPUB,
            'script_type' => 'bip86',
        ]);

        $this->assertSame('bip86', $setting->fresh()->script_type);
    }

    public function test_user_wallet_account_script_type_defaults_to_bip84(): void
    {
        $user = User::factory()->create();

        $account = UserWalletAccount::create([
            'user_id' => $user->id,
            'label' => 'Invoices',
            'network' => 'testnet',
            'bip84_xpub' => self::TESTNET_TPUB,
            'active' => true,
        ]);

        $this->assertSame('bip84', $account->fresh()->script_type);
    }

    public function test_user_wallet_account_persists_bip86_script_type(): void
    {
        $user = User::factory()->create();

        $account = UserWalletAccount::create([
            'user_id' => $user->id,
            'label' => 'Invoices',
            'network' => 'testnet',
            'bip84_xpub' => self::TESTNET_TPUB,
            'active' => true,
            'script_type' => 'bip86',
        ]);

        $this->assertSame('bip86', $account->fresh()->script_type);
    }
}
