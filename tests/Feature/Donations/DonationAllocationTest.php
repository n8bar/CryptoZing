<?php

namespace Tests\Feature\Donations;

use App\Services\DonationAddressAllocator;
use App\Services\HdWallet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DonationAllocationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'donations.xpub' => 'tpubDonationTest',
            'donations.max_unpaid_addresses' => 2,
            'wallet.default_network' => 'testnet4',
        ]);
    }

    public function test_allocates_a_fresh_derived_address_and_persists_a_pending_donation(): void
    {
        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->with('tpubDonationTest', 0, 'testnet4')
                ->once()
                ->andReturn('tb1qdonation0');
        });

        $donation = app(DonationAddressAllocator::class)->allocate(null, 25.00);

        $this->assertSame('tb1qdonation0', $donation->address);
        $this->assertDatabaseHas('donations', [
            'id' => $donation->id,
            'address' => 'tb1qdonation0',
            'derivation_index' => 0,
            'network' => 'testnet4',
            'status' => 'pending',
            'usd_amount_requested' => '25.00',
        ]);
    }

    public function test_existing_pending_donation_is_reused_without_deriving_again(): void
    {
        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->once()
                ->andReturn('tb1qdonation0');
        });

        $allocator = app(DonationAddressAllocator::class);
        $first = $allocator->allocate(null, 25.00);
        $again = $allocator->allocate($first->id, 40.00);

        $this->assertSame($first->id, $again->id);
        $this->assertSame('tb1qdonation0', $again->address);
        $this->assertSame(1, \App\Models\Donation::count());
        $this->assertDatabaseHas('donations', [
            'id' => $first->id,
            'usd_amount_requested' => '40.00',
        ]);
    }

    public function test_pool_cap_reuses_oldest_pending_address_instead_of_deriving_more(): void
    {
        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->with('tpubDonationTest', 0, 'testnet4')
                ->once()
                ->andReturn('tb1qdonation0');
            $mock->shouldReceive('deriveAddress')
                ->with('tpubDonationTest', 1, 'testnet4')
                ->once()
                ->andReturn('tb1qdonation1');
        });

        $allocator = app(DonationAddressAllocator::class);
        $first = $allocator->allocate(null, 10.00);
        $this->travel(1)->minutes();
        $allocator->allocate(null, 15.00);
        $this->travel(1)->minutes();

        $capped = $allocator->allocate(null, 20.00);

        $this->assertSame($first->id, $capped->id);
        $this->assertSame('tb1qdonation0', $capped->address);
        $this->assertSame(2, \App\Models\Donation::count());
    }
}
