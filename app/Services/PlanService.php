<?php

namespace App\Services;

use App\Models\Plan;
use Exception;
use Illuminate\Support\Facades\Log;

class PlanService
{
    /**
     * Get all plans for a gym, grouped by plan_group_name.
     */
    public function getPlansByGym(int $gymId)
    {
        try {
            // Get all plans and group them by plan_group_name
            $plans = Plan::where('gym_id', $gymId)
                ->orderByDesc('id')
                ->get();

            $groupedPlans = [];
            foreach ($plans as $plan) {
                $groupedPlans[$plan->plan_group_name][] = $plan;
            }

            return $groupedPlans;
        } catch (Exception $e) {
            Log::error('PlanService@getPlansByGym Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create single or multiple plans for a gym.
     * Expected data format:
     * [
     *   'plan_group_name' => 'Gold',
     *   'description' => 'Access to all equipment',
     *   'durations' => [
     *      ['duration_months' => 1, 'amount' => 2000],
     *      ['duration_months' => 3, 'amount' => 5000],
     *   ]
     * ]
     */
    public function createPlans(int $gymId, array $data)
    {
        try {
            $createdPlans = [];
            
            foreach ($data['durations'] as $duration) {
                $createdPlans[] = Plan::create([
                    'gym_id' => $gymId,
                    'plan_group_name' => $data['plan_group_name'],
                    'description' => $data['description'] ?? null,
                    'duration_months' => $duration['duration_months'],
                    'amount' => $duration['amount'],
                    'is_active' => true,
                ]);
            }

            return $createdPlans;
        } catch (Exception $e) {
            Log::error('PlanService@createPlans Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing plan.
     */
    public function updatePlan(int $planId, int $gymId, array $data)
    {
        try {
            $plan = Plan::where('id', $planId)->where('gym_id', $gymId)->firstOrFail();
            
            $plan->update([
                'plan_group_name' => $data['plan_group_name'] ?? $plan->plan_group_name,
                'description' => $data['description'] ?? $plan->description,
                'duration_months' => $data['duration_months'] ?? $plan->duration_months,
                'amount' => $data['amount'] ?? $plan->amount,
                'is_active' => $data['is_active'] ?? $plan->is_active,
            ]);

            return $plan;
        } catch (Exception $e) {
            Log::error('PlanService@updatePlan Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a plan.
     */
    public function deletePlan(int $planId, int $gymId)
    {
        try {
            $plan = Plan::where('id', $planId)->where('gym_id', $gymId)->firstOrFail();
            $plan->delete();
            return true;
        } catch (Exception $e) {
            Log::error('PlanService@deletePlan Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
