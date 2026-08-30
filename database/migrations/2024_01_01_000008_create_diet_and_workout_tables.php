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
        // diet_plans
        Schema::create('diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->onDelete('cascade');
            $table->string('title', 150);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // trainer
            $table->boolean('is_template')->default(false);
            $table->timestamps();
        });

        // diet_plan_meals
        Schema::create('diet_plan_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diet_plan_id')->constrained('diet_plans')->onDelete('cascade');
            $table->enum('meal_type', ['breakfast', 'mid_morning', 'lunch', 'evening', 'dinner']);
            $table->text('food_items');
            $table->integer('calories')->nullable();
        });

        // workout_plans
        Schema::create('workout_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gym_id')->constrained('gyms')->onDelete('cascade');
            $table->string('title', 150);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade'); // trainer
            $table->boolean('is_template')->default(false);
            $table->timestamps();
        });

        // workout_plan_days
        Schema::create('workout_plan_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_plan_id')->constrained('workout_plans')->onDelete('cascade');
            $table->string('day_label', 100);
            $table->json('exercises'); // [{name, sets, reps, rest_seconds}]
        });

        // member_diet_plans
        Schema::create('member_diet_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('diet_plan_id')->constrained('diet_plans')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'paused']);
        });

        // member_workout_plans
        Schema::create('member_workout_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('workout_plan_id')->constrained('workout_plans')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['active', 'completed', 'paused']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_workout_plans');
        Schema::dropIfExists('member_diet_plans');
        Schema::dropIfExists('workout_plan_days');
        Schema::dropIfExists('workout_plans');
        Schema::dropIfExists('diet_plan_meals');
        Schema::dropIfExists('diet_plans');
    }
};
