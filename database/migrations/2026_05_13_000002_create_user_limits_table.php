<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// migration for user transaction limits
// prevents users from transferring too much money too fast
// helps prevent fraud and unauthorized transfers

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_limits', function (Blueprint $table) {
            $table->id();

            // which user
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');

            // max amount per single transaction
            $table->decimal('single_transaction_limit', 12, 2)->default(100000);

            // max amount per day
            $table->decimal('daily_transfer_limit', 12, 2)->default(500000);

            // how much they've used today
            $table->decimal('daily_transfer_used', 12, 2)->default(0);

            // when their daily limit resets
            $table->date('limit_reset_date');

            // timestamps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_limits');
    }
};
