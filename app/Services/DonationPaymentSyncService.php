<?php

namespace App\Services;

use App\Mail\DonationReceivedMail;
use App\Models\Donation;
use App\Services\Blockchain\MempoolClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DonationPaymentSyncService
{
    public function __construct(
        private readonly MempoolClient $mempoolClient
    ) {
    }

    /**
     * Check all pending donation addresses for on-chain activity and mark
     * paid on first seen payment. Returns [checked, paid] counts.
     *
     * @return array{checked: int, paid: int}
     */
    public function syncPending(): array
    {
        $pending = Donation::query()
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        if ($pending->isEmpty()) {
            return ['checked' => 0, 'paid' => 0];
        }

        $paidCount = 0;

        foreach ($pending->groupBy('network') as $network => $donations) {
            $transactions = $this->mempoolClient->transactionsForAddresses(
                (string) $network,
                $donations->pluck('address')->all()
            );

            foreach ($donations as $donation) {
                $seen = $this->firstSeenPayment($transactions[$donation->address] ?? [], $donation->address);
                if (! $seen) {
                    continue;
                }

                $donation->forceFill([
                    'status' => 'paid',
                    'txid' => $seen['txid'],
                    'sats_received' => $seen['sats'],
                    'paid_at' => now(),
                ])->save();

                $paidCount++;

                Log::info('donation.payment.detected', [
                    'donation_id' => $donation->id,
                    'txid' => $seen['txid'],
                    'sats' => $seen['sats'],
                ]);

                $notifyEmail = config('donations.notify_email');
                if ($notifyEmail) {
                    Mail::to($notifyEmail)->queue(new DonationReceivedMail($donation));
                }
            }
        }

        return ['checked' => $pending->count(), 'paid' => $paidCount];
    }

    /**
     * @param  array<int, mixed>  $transactions
     * @return array{txid: string, sats: int}|null
     */
    private function firstSeenPayment(array $transactions, string $address): ?array
    {
        foreach ($transactions as $tx) {
            $sats = 0;
            foreach ($tx['vout'] ?? [] as $output) {
                if (($output['scriptpubkey_address'] ?? null) === $address) {
                    $sats += (int) ($output['value'] ?? 0);
                }
            }

            if ($sats > 0 && ! empty($tx['txid'])) {
                return ['txid' => (string) $tx['txid'], 'sats' => $sats];
            }
        }

        return null;
    }
}
