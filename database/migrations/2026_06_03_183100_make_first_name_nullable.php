<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw statement to avoid requiring doctrine/dbal
        DB::statement('ALTER TABLE `users` MODIFY `first_name` VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `users` MODIFY `first_name` VARCHAR(255) NOT NULL');
    }
};
