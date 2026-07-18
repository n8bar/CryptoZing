<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('invoice_deliveries')
            ->whereIn('type', ['client_partial_warning', 'issuer_partial_warning'])
            ->delete();

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('last_partial_warning_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('last_partial_warning_sent_at')->nullable();
        });
    }
};
