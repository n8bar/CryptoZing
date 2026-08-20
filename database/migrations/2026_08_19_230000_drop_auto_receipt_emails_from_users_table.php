<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// auto_receipt_emails shipped with the original automatic-receipt design
// (x8.2) but the truthfulness work made client receipts owner-reviewed and
// manual for RC1 (NOTIFICATIONS.md), leaving the column with zero consumers.
// Dropped rather than built out (#159).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('auto_receipt_emails');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('auto_receipt_emails')->default(true)->after('show_invoice_ids');
        });
    }
};
