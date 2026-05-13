<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Laravel's built-in notifications table
        // Stores in-app notifications in the database
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');     // links to users table
            $table->text('data');             // the notification content (JSON)
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Index to find unread notifications fast
            $table->index(['notifiable_id', 'notifiable_type', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
