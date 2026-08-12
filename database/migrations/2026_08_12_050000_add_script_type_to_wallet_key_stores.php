<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_settings', function (Blueprint $table) {
            $table->string('script_type', 8)->default('bip84')->after('bip84_xpub');
        });

        Schema::table('user_wallet_accounts', function (Blueprint $table) {
            $table->string('script_type', 8)->default('bip84')->after('bip84_xpub');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_settings', function (Blueprint $table) {
            $table->dropColumn('script_type');
        });

        Schema::table('user_wallet_accounts', function (Blueprint $table) {
            $table->dropColumn('script_type');
        });
    }
};
