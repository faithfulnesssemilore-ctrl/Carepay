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
        Schema::table('transactions', function (Blueprint $table) {
            // this column will be used to store the idempotency key for each transaction, ensuring that duplicate transactions can be identified and prevented.
            $table->string('idempotency_key')->nullable()->index()->after('reference');
            $table->unique('idempotency_key'); // Ensure that each idempotency key is unique across the transactions table

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']); // Remove the unique constraint on the idempotency_key column
            $table->dropColumn('idempotency_key'); // Drop the idempotency_key column from the transactions table
        });
    }
};
