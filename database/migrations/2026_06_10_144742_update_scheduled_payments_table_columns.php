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
        Schema::table('scheduled_payments', function (Blueprint $table) {
            // Added missing columns that  don't exist affecting wallet
            if (! Schema::hasColumn('scheduled_payments', 'user_id')) {
                $table->foreignId('user_id')->after('id')->constrained('users')->onDelete('cascade');
            }
            if (! Schema::hasColumn('scheduled_payments', 'bank_account_id')) {
                $table->foreignId('bank_account_id')->nullable()->after('wallet_id')->constrained('bank_accounts')->onDelete('set null');
            }
            if (! Schema::hasColumn('scheduled_payments', 'recipient_id')) {
                $table->foreignId('recipient_id')->nullable()->after('bank_account_id')->constrained('users')->onDelete('set null');
            }
            if (! Schema::hasColumn('scheduled_payments', 'scheduled_date')) {
                $table->dateTime('scheduled_date')->nullable()->after('currency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scheduled_payments', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['user_id']);
            $table->dropForeignKeyIfExists(['bank_account_id']);
            $table->dropForeignKeyIfExists(['recipient_id']);
            $table->dropColumnIfExists(['user_id', 'bank_account_id', 'recipient_id', 'scheduled_date']);
        });
    }
};
