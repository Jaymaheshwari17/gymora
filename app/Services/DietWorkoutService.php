<?php

namespace App\Services;

use App\Models\DietPlan;
use App\Models\DietPlanMeal;
use App\Models\MemberDietPlan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DietWorkoutService
{
    public function createDietPlanTemplate(int $gymId, int $trainerId, array $data)
    {
        DB::beginTransaction();
        try {
            $dietPlan = DietPlan::create([
                'gym_id' => $gymId,
                'title' => $data['title'],
                'created_by' => $trainerId,
                'is_template' => true,
            ]);

            foreach ($data['meals'] as $meal) {
                DietPlanMeal::create([
                    'diet_plan_id' => $dietPlan->id,
                    'meal_type' => $meal['meal_type'],
                    'food_items' => $meal['food_items'],
                    'calories' => $meal['calories'] ?? null,
                ]);
            }

            DB::commit();
            return $dietPlan->load('meals');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DietWorkoutService@createDietPlanTemplate Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function assignDietPlan(int $memberId, int $dietPlanId, int $assignedBy, string $startDate)
    {
        try {
            return MemberDietPlan::create([
                'member_id' => $memberId,
                'diet_plan_id' => $dietPlanId,
                'assigned_by' => $assignedBy,
                'start_date' => $startDate,
                'status' => 'active',
            ]);
        } catch (Exception $e) {
            Log::error('DietWorkoutService@assignDietPlan Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
