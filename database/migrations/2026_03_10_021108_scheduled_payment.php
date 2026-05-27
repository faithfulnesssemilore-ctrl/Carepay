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
        Schema::create('scheduled_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('wallet_id')->constrained('wallet')->onDelete('cascade');
            $table->bigInteger('amount');
            $table->string('currency')->default('NGN');
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->index();
            $table->dateTime('next_payment_date')->index();
            $table->enum('status', ['active', 'pending', 'failed', 'completed'])->default('active')->index();
            $table->string('description')->nullable();
            $table->timestamps();
            // e.g., 'bill_payment', 'subscription', etc.
            $table->string('type')->index();
            $table->json('metadata')->nullable(); // For storing additional info as JSON

            $table->index(['wallet_id', 'next_payment_date']);
            $table->index(['wallet_id', 'status']);
            $table->index(['wallet_id', 'frequency']);
            $table->index(['frequency', 'status']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_payment');
    }
};
