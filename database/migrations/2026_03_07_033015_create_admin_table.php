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
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');

            $table->string('id_document')->nullable();
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->boolean('kyc_verified')->default(false);
            $table->string('transaction_pin')->nullable();
            $table->boolean('registration_complete')->default(false);
            $table->boolean('terms_accepted')->default(false);
            $table->boolean('email_verified')->default(false);
            $table->string('verification_code')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('role')->default('user'); // user, admin

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
