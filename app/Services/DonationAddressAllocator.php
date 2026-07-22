<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\DB;

class DonationAddressAllocator
{
    private const LOCK_NAME = 'cz:donations:allocate';

    public function __construct(
        private readonly HdWallet $hdWallet
    ) {
    }

    /**
     * Return the pending donation for $donationId if it is still open on the
     * current network, otherwise derive a fresh address while the unpaid pool
     * is under the cap. $unit is 'usd' or 'btc'; the amount is stored in its
     * own unit column and the other is cleared. Returns null when the pool is
     * full (bot/gap-limit guard) — addresses are never shared between donor
     * sessions.
     */
    public function allocate(?int $donationId, string $unit, float $amount): ?Donation
    {
        $network = (string) config('wallet.default_network', 'testnet');

        return $this->withAllocationLock(function () use ($donationId, $unit, $amount, $network) {
            if ($donationId) {
                $existing = Donation::query()
                    ->whereKey($donationId)
                    ->where('status', 'pending')
                    ->where('network', $network)
                    ->first();

                if ($existing) {
                    return $this->touchAllocation($existing, $unit, $amount);
                }
            }

            $cap = max((int) config('donations.max_unpaid_addresses', 20), 1);
            $pendingCount = Donation::query()
                ->where('status', 'pending')
                ->where('network', $network)
                ->count();

            if ($pendingCount >= $cap) {
                return null;
            }

            $index = (int) (Donation::query()->where('network', $network)->max('derivation_index') ?? -1) + 1;
            $address = $this->hdWallet->deriveAddress((string) config('donations.xpub'), $index, $network);

            return Donation::query()->create(array_merge([
                'derivation_index' => $index,
                'address' => $address,
                'network' => $network,
                'status' => 'pending',
                'allocated_at' => now(),
            ], $this->amountColumns($unit, $amount)));
        });
    }

    /**
     * Serialize allocation via a MySQL advisory lock so concurrent requests
     * cannot read the same max index or both squeeze past the cap. A donor
     * who cannot get the lock within the wait window is treated as pool-busy.
     */
    private function withAllocationLock(\Closure $callback): ?Donation
    {
        $acquired = (int) (DB::selectOne('SELECT GET_LOCK(?, 5) AS acquired', [self::LOCK_NAME])->acquired ?? 0);

        if ($acquired !== 1) {
            return null;
        }

        try {
            return $callback();
        } finally {
            DB::selectOne('SELECT RELEASE_LOCK(?) AS released', [self::LOCK_NAME]);
        }
    }

    private function touchAllocation(Donation $donation, string $unit, float $amount): Donation
    {
        $donation->forceFill(array_merge(
            ['allocated_at' => now()],
            $this->amountColumns($unit, $amount)
        ))->save();

        return $donation;
    }

    /**
     * @return array{usd_amount_requested: float|null, btc_amount_requested: float|null}
     */
    private function amountColumns(string $unit, float $amount): array
    {
        return $unit === 'btc'
            ? ['usd_amount_requested' => null, 'btc_amount_requested' => $amount]
            : ['usd_amount_requested' => $amount, 'btc_amount_requested' => null];
    }
}
