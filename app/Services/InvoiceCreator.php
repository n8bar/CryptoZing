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

    /** Create the invoice on the wallet's next address and store its lines. */
    public function create(array $data, WalletSetting $wallet, int $userId, ?array $preparedLineage = null, array $lines = []): Invoice
    {
        $invoice = $this->lineage->withPreparedAssignment($wallet, $preparedLineage, function (array $lineage) use ($data, $userId, $wallet) {
            return Invoice::create($data + [
                'user_id' => $userId,
            ] + $lineage + $wallet->invoiceUnsupportedConfigurationSnapshot());
        });

        if ($lines !== []) {
            InvoiceLines::replaceFor($invoice, $lines);
        }

        return $invoice;
    }
}
