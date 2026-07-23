<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Email 2FA is enabled iff this timestamp is non-null (doubles as an audit stamp).
            if (! Schema::hasColumn('users', 'two_factor_email_enabled_at')) {
                $table->timestamp('two_factor_email_enabled_at')->nullable()->after('password');
            }

            // Shared challenge code state (enrollment, login challenge, TOTP email fallback).
            if (! Schema::hasColumn('users', 'two_factor_code_hash')) {
                $table->string('two_factor_code_hash')->nullable()->after('two_factor_email_enabled_at');
            }

            if (! Schema::hasColumn('users', 'two_factor_code_expires_at')) {
                $table->timestamp('two_factor_code_expires_at')->nullable()->after('two_factor_code_hash');
            }

            // Shared attempt counter + lockout window (guessing protection at the challenge).
            if (! Schema::hasColumn('users', 'two_factor_attempts')) {
                $table->unsignedTinyInteger('two_factor_attempts')->default(0)->after('two_factor_code_expires_at');
            }

            if (! Schema::hasColumn('users', 'two_factor_locked_until')) {
                $table->timestamp('two_factor_locked_until')->nullable()->after('two_factor_attempts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach ([
                'two_factor_locked_until',
                'two_factor_attempts',
                'two_factor_code_expires_at',
                'two_factor_code_hash',
                'two_factor_email_enabled_at',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
