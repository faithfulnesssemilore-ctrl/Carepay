<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// migration to create audit logs table
// stores every financial action for compliance and fraud detection
// required by nigerian banking regulators (CBN)

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // who did the action
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // what action (transfer_sent, deposit_received, etc)
            $table->string('action');

            // what kind of thing was affected (Transaction, Wallet, etc)
            $table->string('entity_type')->nullable();

            // the id of that thing
            $table->unsignedBigInteger('entity_id')->nullable();

            // what changed (old value and new value)
            $table->json('changes')->nullable();

            // where from (ip address)
            $table->string('ip_address')->nullable();

            // what device/browser
            $table->text('user_agent')->nullable();

            // when it happened
            $table->timestamps();

            // indexes for fast lookups
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
