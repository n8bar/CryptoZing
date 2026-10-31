<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('api_id', 32)->nullable()->unique()->after('id');
        });

        DB::table('invoices')->whereNull('api_id')->orderBy('id')->each(function ($invoice) {
            DB::table('invoices')->where('id', $invoice->id)->update(['api_id' => 'inv_' . Str::lower(Str::random(12))]);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['api_id']);
            $table->dropColumn('api_id');
        });
    }
};
