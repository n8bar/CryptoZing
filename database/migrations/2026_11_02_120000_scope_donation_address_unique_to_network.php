<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Signet derives the same address strings as testnet, so an address is only
// unique within its network.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropUnique(['address']);
            $table->unique(['network', 'address']);
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropUnique(['network', 'address']);
            $table->unique('address');
        });
    }
};
