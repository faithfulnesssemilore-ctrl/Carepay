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
        // Update transaction type enum to use credit/debit instead of deposit/withdrawal/transfer
        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE transactions MODIFY COLUMN type ENUM('credit', 'debit') NOT NULL"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old enum values
        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit', 'withdrawal', 'transfer') NOT NULL"
        );
    }
};
