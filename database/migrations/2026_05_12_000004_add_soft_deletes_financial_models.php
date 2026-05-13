<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // same thing but for bank accounts and virtual accounts
    // when deleted, they stay in db for history/audit
    
    public function up(): void
    {
        // bank accounts - soft delete
        if (!Schema::hasColumn('bank_accounts', 'deleted_at')) {
            Schema::table('bank_accounts', function (Blueprint $table) {
                $table->softDeletes();
                $table->index('user_id');
            });
        }

        // virtual accounts - soft delete
        if (!Schema::hasColumn('virtual_accounts', 'deleted_at')) {
            Schema::table('virtual_accounts', function (Blueprint $table) {
                $table->softDeletes();
                $table->index('user_id');
            });
        }

        // wallet - soft delete too
        if (Schema::hasTable('wallets') && !Schema::hasColumn('wallets', 'deleted_at')) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->softDeletes();
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bank_accounts', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['user_id']);
        });

        Schema::table('virtual_accounts', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['user_id']);
        });

        if (Schema::hasTable('wallets')) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->dropSoftDeletes();
                $table->dropIndex(['user_id']);
            });
        }
    }
};
