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
        Schema::create('gyms', function (Blueprint $table) {
            $table->id();
            // owner_id will be foreign key to users.id. 
            // We can't add constraint yet because users table might not exist.
            $table->unsignedBigInteger('owner_id')->nullable(); 
            $table->string('name', 150);
            $table->string('gym_code', 20)->unique();
            $table->string('logo', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gyms');
    }
};
