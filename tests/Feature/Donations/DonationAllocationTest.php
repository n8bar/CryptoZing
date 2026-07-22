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

        $donation = app(DonationAddressAllocator::class)->allocate(null, 'usd', 25.00);

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
        $first = $allocator->allocate(null, 'usd', 25.00);
        $again = $allocator->allocate($first->id, 'usd', 40.00);

        $this->assertSame($first->id, $again->id);
        $this->assertSame('tb1qdonation0', $again->address);
        $this->assertSame(1, \App\Models\Donation::count());
        $this->assertDatabaseHas('donations', [
            'id' => $first->id,
            'usd_amount_requested' => '40.00',
        ]);
    }

    public function test_pool_cap_refuses_new_allocations_instead_of_sharing_addresses(): void
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
        $first = $allocator->allocate(null, 'usd', 10.00);
        $allocator->allocate(null, 'usd', 15.00);

        $capped = $allocator->allocate(null, 'usd', 20.00);

        $this->assertNull($capped);
        $this->assertSame(2, \App\Models\Donation::count());
        $this->assertSame('10.00', (string) $first->fresh()->usd_amount_requested);
    }

    public function test_allocation_queries_are_scoped_to_the_current_network(): void
    {
        \App\Models\Donation::query()->create([
            'derivation_index' => 9,
            'address' => 'tb1qoldtestnet',
            'network' => 'testnet',
            'status' => 'pending',
            'allocated_at' => now(),
        ]);

        $this->mock(HdWallet::class, function ($mock) {
            $mock->shouldReceive('deriveAddress')
                ->with('tpubDonationTest', 0, 'testnet4')
                ->once()
                ->andReturn('tb1qnet0');
        });

        config(['donations.max_unpaid_addresses' => 1]);

        $donation = app(DonationAddressAllocator::class)->allocate(null, 'usd', 25.00);

        $this->assertNotNull($donation);
        $this->assertSame('tb1qnet0', $donation->address);
        $this->assertSame(0, $donation->derivation_index);
        $this->assertSame('testnet4', $donation->network);
    }
}
