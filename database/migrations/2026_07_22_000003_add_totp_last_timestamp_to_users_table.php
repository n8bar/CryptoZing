<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Last accepted TOTP time-step, so a code can't be replayed within
            // its validity window (google2fa verifyKeyNewer).
            if (! Schema::hasColumn('users', 'two_factor_totp_last_timestamp')) {
                $table->unsignedBigInteger('two_factor_totp_last_timestamp')->nullable()->after('two_factor_totp_confirmed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'two_factor_totp_last_timestamp')) {
                $table->dropColumn('two_factor_totp_last_timestamp');
            }
        });
    }
};
