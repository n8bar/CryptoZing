<?php

namespace App\Services;

use App\Models\Invoice;

/**
 * Give every invoice without lines one line that reproduces its amount:
 * its description, quantity 1, and its USD amount as the rate.
 */
class BackfillInvoiceLines
{
    public function run(): int
    {
        $count = 0;

        Invoice::withTrashed()->whereDoesntHave('lines')->chunkById(200, function ($invoices) use (&$count) {
            foreach ($invoices as $invoice) {
                $invoice->lines()->create([
                    'position'    => 1,
                    'description' => $invoice->description ?: $invoice->number,
                    'quantity'    => 1,
                    'rate_usd'    => $invoice->amount_usd,
                ]);
                $count++;
            }
        });

        return $count;
    }
}
