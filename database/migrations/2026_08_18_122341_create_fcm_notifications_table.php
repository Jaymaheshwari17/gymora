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
        Schema::create('fcm_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('body');
            $table->json('data')->nullable(); // For any extra payload
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            // Indexes for faster querying
            $table->index('user_id');
            $table->index('is_read');
            $table->index('created_at');
            
            // Composite index for getting unread notifications of a user fast
            $table->index(['user_id', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_notifications');
    }
};
