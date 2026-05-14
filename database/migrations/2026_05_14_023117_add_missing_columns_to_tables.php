<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Add missing columns to complete the schema

    public function up(): void
    {
        // Add kyc_rejection_reason to users table
        if (!Schema::hasColumn('users', 'kyc_rejection_reason')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('kyc_rejection_reason')->nullable();
            });
        }

        // Add recipient_id to transactions table
        if (!Schema::hasColumn('transactions', 'recipient_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('set null');
            });
        }

        // Update transactions enum to include 'credit' and 'debit' if not already
        // This is handled separately due to migration complexity
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'kyc_rejection_reason')) {
                $table->dropColumn('kyc_rejection_reason');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'recipient_id')) {
                $table->dropConstrainedForeignId('recipient_id');
            }
        });
    }
};
