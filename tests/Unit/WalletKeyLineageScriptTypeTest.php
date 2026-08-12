<?php

namespace Tests\Unit;

use App\Models\WalletSetting;
use App\Services\HdWallet;
use App\Services\WalletKeyLineage;
use Mockery;
use Tests\TestCase;

class WalletKeyLineageScriptTypeTest extends TestCase
{
    private const TPUB = 'tpubDCMX5n5xeyKFQ1R98FTjQ21An9e2SgN8gF5pa4DJNfQd8B5CYCqkkWXEmH4YrxRAEDzFSv25yineuGfvFAg9tWJcGakvm7Ft5e41jQZ2bHk';

    public function test_fingerprint_distinguishes_script_types_and_keeps_bip84_stable(): void
    {
        $lineage = app(WalletKeyLineage::class);

        $this->assertSame(
            $lineage->fingerprint('testnet', self::TPUB),
            $lineage->fingerprint('testnet', self::TPUB, 'bip84')
        );
        $this->assertNotSame(
            $lineage->fingerprint('testnet', self::TPUB, 'bip84'),
            $lineage->fingerprint('testnet', self::TPUB, 'bip86')
        );
    }

    public function test_derive_invoice_lineage_uses_wallet_script_type(): void
    {
        $hdWallet = Mockery::mock(HdWallet::class);
        $hdWallet->shouldReceive('deriveAddress')
            ->once()
            ->with(self::TPUB, 7, 'testnet', 'bip86')
            ->andReturn('tb1ptestaddress0000000000000000000000000000000000000000000000');

        $lineage = new WalletKeyLineage($hdWallet);

        $wallet = new WalletSetting([
            'network' => 'testnet',
            'bip84_xpub' => self::TPUB,
            'script_type' => 'bip86',
        ]);

        $result = $lineage->deriveInvoiceLineage($wallet, 7);

        $this->assertSame('tb1ptestaddress0000000000000000000000000000000000000000000000', $result['payment_address']);
        $this->assertSame($lineage->fingerprint('testnet', self::TPUB, 'bip86'), $result['wallet_key_fingerprint']);
    }
}
