<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\WalletSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UnsupportedConfigurationEvidenceService
{
    public function __construct(
        private readonly WalletKeyLineage $lineage
    ) {
    }

    /**
     * @param  array<int, array{txid: string, sats: int}>  $payments
     */
    public function flagPaymentCollisionEvidence(Invoice $invoice, array $payments): void
    {
        if (! $invoice->payment_address || ! $invoice->wallet_network || $payments === []) {
            return;
        }

        $implicatedInvoices = Invoice::query()
            ->where('payment_address', $invoice->payment_address)
            ->where('wallet_network', $invoice->wallet_network)
            ->get([
                'id',
                'user_id',
                'number',
                'payment_address',
                'wallet_key_fingerprint',
                'wallet_network',
                'unsupported_configuration_flagged',
                'unsupported_configuration_reason',
            ]);

        if ($implicatedInvoices->count() <= 1) {
            return;
        }

        $flaggedAt = now();
        $txid = (string) (collect($payments)->pluck('txid')->filter()->first() ?? 'unknown');
        $invoiceReferences = $implicatedInvoices
            ->mapWithKeys(fn (Invoice $candidate) => [$candidate->id => $this->invoiceReference($candidate)]);
        $newlyFlagged = false;

        foreach ($implicatedInvoices as $implicatedInvoice) {
            $otherInvoiceReferences = $invoiceReferences
                ->except($implicatedInvoice->id)
                ->values();

            $details = $this->detailsForInvoice(
                $implicatedInvoice,
                $otherInvoiceReferences,
                $txid,
                (string) $invoice->payment_address,
            );

            $alreadyFlagged = $implicatedInvoice->unsupported_configuration_flagged
                && $implicatedInvoice->unsupported_configuration_reason === 'payment_collision';

            if (! $alreadyFlagged) {
                $implicatedInvoice->markUnsupportedConfiguration(
                    source: 'evidence',
                    reason: 'payment_collision',
                    details: $details,
                    flaggedAt: $flaggedAt,
                );
                $newlyFlagged = true;
            }

            if ($this->flagCurrentWalletIfLineageMatches($implicatedInvoice, $details, $flaggedAt)) {
                $newlyFlagged = true;
            }
        }

        if (! $newlyFlagged) {
            return;
        }

        Log::warning('invoice.unsupported_configuration.payment_collision', [
            'invoice_ids' => $implicatedInvoices->pluck('id')->all(),
            'address' => $invoice->payment_address,
            'network' => $invoice->wallet_network,
            'txid' => $txid,
        ]);
    }

    /**
     * @param  Collection<int, string>  $otherInvoiceReferences
     */
    private function detailsForInvoice(
        Invoice $invoice,
        Collection $otherInvoiceReferences,
        string $txid,
        string $address
    ): string {
        $references = $otherInvoiceReferences->implode(', ');
        $currentReference = $this->invoiceReference($invoice);

        return "Detected payment tx {$txid} on address {$address} for {$currentReference}, which is also assigned to {$references}.";
    }

    private function flagCurrentWalletIfLineageMatches(Invoice $invoice, string $details, Carbon $flaggedAt): bool
    {
        if (! $invoice->wallet_key_fingerprint || ! $invoice->wallet_network) {
            return false;
        }

        $wallet = WalletSetting::query()
            ->where('user_id', $invoice->user_id)
            ->first();

        if (! $wallet?->bip84_xpub) {
            return false;
        }

        if ($wallet->unsupported_configuration_active && $wallet->unsupported_configuration_reason === 'payment_collision') {
            return false;
        }

        $currentNetwork = $this->lineage->normalizeNetwork((string) $wallet->network);
        $currentFingerprint = $this->lineage->fingerprint($currentNetwork, (string) $wallet->bip84_xpub, (string) ($wallet->script_type ?? 'bip84'));

        if ($currentNetwork !== $invoice->wallet_network || $currentFingerprint !== $invoice->wallet_key_fingerprint) {
            return false;
        }

        $wallet->markUnsupportedConfiguration(
            source: 'evidence',
            reason: 'payment_collision',
            details: $details,
            flaggedAt: $flaggedAt,
        );

        return true;
    }

    private function invoiceReference(Invoice $invoice): string
    {
        return $invoice->number
            ? "invoice {$invoice->number} (#{$invoice->id})"
            : "invoice #{$invoice->id}";
    }
}
