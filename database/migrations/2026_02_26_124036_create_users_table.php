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
    Schema::create('users', function (Blueprint $table) {
    $table->id();

    $table->string('first_name');
    $table->string('last_name');
    $table->string('email')->unique();
    $table->string('phone')->nullable();
    $table->string('password');  

    // KYC
    $table->string('id_document')->nullable();
    $table->string('id_type')->nullable();
    $table->string('id_number')->nullable();
    $table->boolean('kyc_verified')->default(false);

    // Security
    $table->string('pin')->nullable();

    // Registration
    $table->boolean('registration_complete')->default(false);
    $table->boolean('terms_accepted')->default(false);

    // Verification
    $table->timestamp('email_verified_at')->nullable();
    $table->string('verification_code')->nullable();

    // Profile
    $table->string('profile_picture')->nullable();

    // Roles
    $table->string('role')->default('user'); // FIXED

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
