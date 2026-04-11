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
    Schema::create('transactions', function (Blueprint $table) {
        $table->id(); // Transaction's own ID can stay an Integer
        $table->uuid('reference')->unique();
        
        // FIX: Change this to foreignUuid to match the wallet table
        $table->foreignUuid('wallet_id')->constrained('wallet')->onDelete('cascade');

        $table->enum('type', ['deposit', 'withdrawal', 'transfer'])->index();
        $table->bigInteger('amount');
        $table->string('currency')->default('NGN');
        $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();
        $table->string('description')->nullable();
        $table->string('metadata')->nullable(); 
        $table->timestamps();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();

$table->string('payment_method')->nullable();
$table->string('gateway')->nullable();

        // These indexes will now work with the UUID
        $table->index(['wallet_id','type']);
        $table->index(['wallet_id','status']);
        $table->index(['type','status']);
        $table->index(['wallet_id','type','status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
