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
     * Check all pending donation addresses for on-chain activity, mark paid
     * with the summed total across every tx paying the address, and queue the
     * operator notification. Notification is convergent: paid rows that never
     * got their mail queued (queue outage) are retried on the next run.
     *
     * @return array{checked: int, paid: int}
     */
    public function syncPending(): array
    {
        $pending = Donation::query()
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        $newlyPaid = [];

        foreach ($pending->groupBy('network') as $network => $donations) {
            $transactions = $this->mempoolClient->transactionsForAddresses(
                (string) $network,
                $donations->pluck('address')->all()
            );

            foreach ($donations as $donation) {
                $seen = $this->seenPaymentTotal($transactions[$donation->address] ?? [], $donation->address);
                if (! $seen) {
                    continue;
                }

                $donation->forceFill([
                    'status' => 'paid',
                    'txid' => $seen['txid'],
                    'sats_received' => $seen['sats'],
                    'paid_at' => now(),
                ])->save();

                $newlyPaid[] = $donation;

                Log::info('donation.payment.detected', [
                    'donation_id' => $donation->id,
                    'txid' => $seen['txid'],
                    'sats' => $seen['sats'],
                ]);
            }
        }

        $unnotified = Donation::query()
            ->where('status', 'paid')
            ->whereNull('notified_at')
            ->orderBy('id')
            ->get();

        foreach ($unnotified as $donation) {
            $this->notifyOperator($donation);
        }

        return ['checked' => $pending->count(), 'paid' => count($newlyPaid)];
    }

    private function notifyOperator(Donation $donation): void
    {
        $notifyEmail = config('donations.notify_email');
        if (! $notifyEmail) {
            return;
        }

        try {
            Mail::to($notifyEmail)->queue(new DonationReceivedMail($donation));
            $donation->forceFill(['notified_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::warning('donation.notify.queue_failed', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Total sats across every transaction paying this address, with the txid
     * of the first paying tx in the response.
     *
     * @param  array<int, mixed>  $transactions
     * @return array{txid: string, sats: int}|null
     */
    private function seenPaymentTotal(array $transactions, string $address): ?array
    {
        $total = 0;
        $txid = null;

        foreach ($transactions as $tx) {
            $sats = 0;
            foreach ($tx['vout'] ?? [] as $output) {
                if (($output['scriptpubkey_address'] ?? null) === $address) {
                    $sats += (int) ($output['value'] ?? 0);
                }
            }

            if ($sats > 0 && ! empty($tx['txid'])) {
                $total += $sats;
                $txid ??= (string) $tx['txid'];
            }
        }

        return $total > 0 && $txid !== null ? ['txid' => $txid, 'sats' => $total] : null;
    }
}
