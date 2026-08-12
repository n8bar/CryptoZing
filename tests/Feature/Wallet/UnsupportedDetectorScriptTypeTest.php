<?php

namespace Tests\Feature\Wallet;

use App\Models\User;
use App\Models\WalletSetting;
use App\Services\Blockchain\MempoolClient;
use App\Services\HdWallet;
use App\Services\WalletUnsupportedConfigurationDetector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnsupportedDetectorScriptTypeTest extends TestCase
{
    use RefreshDatabase;

    private const TPUB = 'tpubDCMX5n5xeyKFQ1R98FTjQ21An9e2SgN8gF5pa4DJNfQd8B5CYCqkkWXEmH4YrxRAEDzFSv25yineuGfvFAg9tWJcGakvm7Ft5e41jQZ2bHk';

    public function test_proactive_scan_derives_with_the_wallet_script_type(): void
    {
        $user = User::factory()->create();
        $wallet = WalletSetting::create([
            'user_id' => $user->id,
            'network' => 'testnet',
            'bip84_xpub' => self::TPUB,
            'script_type' => 'bip86',
            'onboarded_at' => now(),
        ]);

        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->atLeast()->once()
                ->withArgs(fn ($key, $index, $network, $scriptType = 'bip84') => $key === self::TPUB
                    && $network === 'testnet'
                    && $scriptType === 'bip86')
                ->andReturnUsing(fn ($key, $index) => 'tb1pscan' . $index);
        });

        $this->mock(MempoolClient::class, function ($mock) {
            $mock->shouldReceive('transactionsForAddresses')->andReturn([]);
        });

        $finding = app(WalletUnsupportedConfigurationDetector::class)
            ->detectProactiveOutsideReceiveActivity($wallet->fresh());

        $this->assertNull($finding);
    }
}
