<?php

use App\Services\BackfillInvoiceLines;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new BackfillInvoiceLines())->run();
    }

    public function down(): void
    {
        // The create_invoice_lines migration drops the table.
    }
};
