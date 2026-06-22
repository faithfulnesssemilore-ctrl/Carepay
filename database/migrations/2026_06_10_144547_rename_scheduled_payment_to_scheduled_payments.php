<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only rename if the old table exists
        if (Schema::hasTable('scheduled_payment')) {
            Schema::rename('scheduled_payment', 'scheduled_payments');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only rename if the new table exists
        if (Schema::hasTable('scheduled_payments')) {
            Schema::rename('scheduled_payments', 'scheduled_payment');
        }
    }
};
