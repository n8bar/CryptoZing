<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\DB;

class DonationAddressAllocator
{
    public function __construct(
        private readonly HdWallet $hdWallet
    ) {
    }

    /**
     * Return the pending donation for $donationId if it is still open,
     * otherwise allocate an address: derive fresh while the unpaid pool
     * is under the cap, then fall back to reusing the oldest pending
     * address (bot/gap-limit guard).
     */
    public function allocate(?int $donationId, float $usdAmount): Donation
    {
        return DB::transaction(function () use ($donationId, $usdAmount) {
            if ($donationId) {
                $existing = Donation::query()
                    ->whereKey($donationId)
                    ->where('status', 'pending')
                    ->first();

                if ($existing) {
                    return $this->touchAllocation($existing, $usdAmount);
                }
            }

            $cap = max((int) config('donations.max_unpaid_addresses', 20), 1);
            $pendingCount = Donation::query()->where('status', 'pending')->count();

            if ($pendingCount >= $cap) {
                $oldest = Donation::query()
                    ->where('status', 'pending')
                    ->orderBy('allocated_at')
                    ->orderBy('id')
                    ->first();

                return $this->touchAllocation($oldest, $usdAmount);
            }

            $index = (int) (Donation::query()->max('derivation_index') ?? -1) + 1;
            $network = (string) config('wallet.default_network', 'testnet');
            $address = $this->hdWallet->deriveAddress((string) config('donations.xpub'), $index, $network);

            return Donation::query()->create([
                'derivation_index' => $index,
                'address' => $address,
                'network' => $network,
                'usd_amount_requested' => $usdAmount,
                'status' => 'pending',
                'allocated_at' => now(),
            ]);
        });
    }

    private function touchAllocation(Donation $donation, float $usdAmount): Donation
    {
        $donation->forceFill([
            'usd_amount_requested' => $usdAmount,
            'allocated_at' => now(),
        ])->save();

        return $donation;
    }
}
