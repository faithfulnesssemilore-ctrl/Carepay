<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Add performance indexes to speed up database queries

    public function up(): void
    {
        // Transactions indexes for fast lookups
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasIndex('transactions', 'transactions_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at']);
            }
            if (!Schema::hasIndex('transactions', 'transactions_reference_unique')) {
                $table->unique('reference');
            }
            if (!Schema::hasIndex('transactions', 'transactions_status_index')) {
                $table->index('status');
            }
        });

        // Wallet indexes for user lookup
        Schema::table('wallet', function (Blueprint $table) {
            if (!Schema::hasIndex('wallet', 'wallet_user_id_index')) {
                $table->index('user_id');
            }
        });

        // Users indexes for auth and searches
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'users_email_unique')) {
                $table->unique('email');
            }
            if (!Schema::hasIndex('users', 'users_username_unique')) {
                $table->unique('username');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasIndex('transactions', 'transactions_user_id_created_at_index')) {
                $table->dropIndex('transactions_user_id_created_at_index');
            }
            if (Schema::hasIndex('transactions', 'transactions_status_index')) {
                $table->dropIndex('transactions_status_index');
            }
        });

        Schema::table('wallet', function (Blueprint $table) {
            if (Schema::hasIndex('wallet', 'wallet_user_id_index')) {
                $table->dropIndex('wallet_user_id_index');
            }
        });
    }
};
