<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('derivation_index')->unique();
            $table->string('address')->unique();
            $table->string('network');
            $table->decimal('usd_amount_requested', 10, 2)->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('txid')->nullable();
            $table->unsignedBigInteger('sats_received')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('allocated_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
