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
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('gym_id')->constrained('gyms')->onDelete('cascade');
            
            $table->date('date');
            $table->enum('status', ['P', 'A']); // Present, Absent
            
            $table->foreignId('marked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->time('check_in_time')->nullable();
            
            $table->timestamps();
            
            // One record per member per day
            $table->unique(['member_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
