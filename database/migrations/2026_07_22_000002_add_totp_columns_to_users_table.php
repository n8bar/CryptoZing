<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Encrypted at rest (not hashed): the server reads it back to verify
            // every TOTP login. Enabled iff two_factor_totp_confirmed_at is set —
            // an unconfirmed secret never gates login.
            if (! Schema::hasColumn('users', 'two_factor_totp_secret')) {
                $table->text('two_factor_totp_secret')->nullable()->after('two_factor_locked_until');
            }

            if (! Schema::hasColumn('users', 'two_factor_totp_confirmed_at')) {
                $table->timestamp('two_factor_totp_confirmed_at')->nullable()->after('two_factor_totp_secret');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['two_factor_totp_confirmed_at', 'two_factor_totp_secret'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
