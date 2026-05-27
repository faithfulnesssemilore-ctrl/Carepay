<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ledgerentries', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->index();

            // 1. Changed 'wallet' to 'wallets' (plural)
            $table->foreignUuid('wallet_id')->constrained('wallet')->cascadeOnDelete();

            // 2. Changed 'transaction' to 'transactions' (plural)
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();

            $table->enum('entry_type', ['debit', 'credit']);
            $table->bigInteger('amount');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->bigInteger('balance_before');
            $table->bigInteger('balance_after');

            $table->index(['wallet_id', 'entry_type']);
            $table->index(['transaction_id', 'entry_type']);
            $table->index(['wallet_id', 'transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgerentries');
    }
};
