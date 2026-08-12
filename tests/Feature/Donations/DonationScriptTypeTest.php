<?php

namespace Tests\Feature\Donations;

use App\Services\DonationAddressAllocator;
use App\Services\HdWallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DonationScriptTypeTest extends TestCase
{
    use DatabaseTransactions;

    private const TPUB = 'tpubDCMX5n5xeyKFQ1R98FTjQ21An9e2SgN8gF5pa4DJNfQd8B5CYCqkkWXEmH4YrxRAEDzFSv25yineuGfvFAg9tWJcGakvm7Ft5e41jQZ2bHk';

    public function test_tr_descriptor_donation_key_derives_taproot(): void
    {
        config([
            'donations.xpub' => 'tr(' . self::TPUB . '/0/*)',
            'donations.max_unpaid_addresses' => 2,
            'wallet.default_network' => 'testnet4',
        ]);

        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->with(self::TPUB, 0, 'testnet4', 'bip86')
                ->once()
                ->andReturn('tb1pdonation000000000000000000000000000000000000000000000000');
        });

        $donation = app(DonationAddressAllocator::class)->allocate(null, 'usd', 5.0);

        $this->assertNotNull($donation);
        $this->assertSame('tb1pdonation000000000000000000000000000000000000000000000000', $donation->address);
    }

    public function test_bare_donation_key_defaults_to_bip84(): void
    {
        config([
            'donations.xpub' => self::TPUB,
            'donations.max_unpaid_addresses' => 2,
            'wallet.default_network' => 'testnet4',
        ]);

        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->with(self::TPUB, 0, 'testnet4', 'bip84')
                ->once()
                ->andReturn('tb1qdonation0000000000000000000000000');
        });

        $donation = app(DonationAddressAllocator::class)->allocate(null, 'usd', 5.0);

        $this->assertNotNull($donation);
        $this->assertSame('tb1qdonation0000000000000000000000000', $donation->address);
    }
}
