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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();

            // Relational Fields
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->constrained('wallet')->onDelete('cascade');

            // Financial Calculations Fields
            $table->enum('type', ['credit', 'debit'])->index();
            $table->string('category')->index();

            // Amount & Currency
            $table->bigInteger('amount'); // Storing in cents/kobo
            $table->string('currency')->default('NGN');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();

            // Metadata & Context
            $table->string('description')->nullable();
            $table->text('metadata')->nullable(); // Changed to text for larger JSON payloads
            $table->string('payment_method')->nullable();
            $table->string('gateway')->nullable();
            $table->timestamps();

            // Compound Indexes for fast queries and frontend display
            $table->index(['user_id', 'status']);
            $table->index(['wallet_id', 'type']);
            $table->index(['wallet_id', 'status']);
            $table->index(['type', 'status']);
            $table->index(['wallet_id', 'type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
