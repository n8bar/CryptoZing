<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\User;
use App\Models\WalletSetting;
use Illuminate\Support\Carbon;

/** Creates invoices the same way for the app and the developer API. */
class InvoiceCreator
{
    public function __construct(private readonly WalletKeyLineage $lineage)
    {
    }

    /** Fill the description and due date from the issuer's invoice defaults when left empty. */
    public function applyDefaults(User $user, array $data): array
    {
        if (empty($data['description']) && !empty($user->invoice_default_description)) {
            $data['description'] = $user->invoice_default_description;
        }

        if (empty($data['due_date']) && $user->invoice_default_terms_days !== null) {
            $referenceDate = !empty($data['invoice_date'])
                ? Carbon::parse($data['invoice_date'])
                : now();
            $data['due_date'] = $referenceDate->copy()
                ->addDays($user->invoice_default_terms_days)
                ->toDateString();
        }

        return $data;
    }

    /** Create the invoice and store its lines; it takes the wallet's next address only if it has something to pay. */
    public function create(array $data, WalletSetting $wallet, int $userId, ?array $preparedLineage = null, array $lines = []): Invoice
    {
        $make = fn (array $lineage) => Invoice::create($data + [
            'user_id' => $userId,
        ] + $lineage + $wallet->invoiceUnsupportedConfigurationSnapshot());

        $invoice = isset($data['amount_usd']) && (float) $data['amount_usd'] <= 0
            ? $make([])
            : $this->lineage->withPreparedAssignment($wallet, $preparedLineage, $make);

        if ($lines !== []) {
            InvoiceLines::replaceFor($invoice, $lines);
        }

        return $invoice;
    }

    /**
     * After an edit: an invoice with something to pay gets an address, and one at
     * $0 or less gives back an address no payment has touched (LINE_ITEMS.md §4).
     */
    public function syncAddress(Invoice $invoice, ?WalletSetting $wallet): void
    {
        if ($invoice->hasNothingToPay()) {
            if ($invoice->payment_address && ! $invoice->sourcePayments()->exists() && ! $invoice->payments()->exists()) {
                $invoice->forceFill(['payment_address' => null, 'derivation_index' => null, 'wallet_key_fingerprint' => null, 'wallet_network' => null])->save();
            }

            return;
        }

        if (! $invoice->payment_address && $wallet) {
            $this->lineage->withNextAssignment($wallet, fn (array $lineage) => $invoice->forceFill($lineage)->save());
        }
    }
}
