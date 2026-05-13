<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // this adds soft deletes to transactions
    // means when we delete a transaction, its still in the database
    // just marked as deleted so we can see history
    
    public function up(): void
    {
        // add deleted_at column if not already there
        if (!Schema::hasColumn('transactions', 'deleted_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->softDeletes(); // adds deleted_at datetime column
            });
        }

        // add useful indexes for searching
        if (!Schema::hasColumn('transactions', 'user_id') || !Schema::hasIndex('transactions', 'user_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('user_id');
                $table->index(['user_id', 'created_at']);
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['status']);
        });
    }
};
