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
        Schema::create('scheduled_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('wallet_id')->constrained('wallet')->onDelete('cascade');
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_account')->onDelete('set null');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('set null');
            $table->bigInteger('amount');
            $table->string('currency')->default('NGN');
            $table->dateTime('scheduled_date')->index();
            $table->enum('status', ['active', 'pending', 'failed', 'completed'])->default('pending')->index();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'scheduled_date']);
            $table->index(['user_id', 'status']);
            $table->index(['wallet_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_payments');
    }
};
