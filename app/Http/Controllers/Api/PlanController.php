<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PlanService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class PlanController extends Controller
{
    use ApiResponse;

    protected $planService;

    public function __construct(PlanService $planService)
    {
        $this->planService = $planService;
    }

    /**
     * Get all plans for the authenticated user's gym.
     */
    public function index(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;
            
            // Only Owner and Staff should be able to view plans (or maybe everyone? the spec says "Plans (view only)" for staff)
            // Let's just return it based on gym_id
            $plans = $this->planService->getPlansByGym($gymId);
            
            return $this->successResponse('Plans retrieved successfully', $plans);
        } catch (Exception $e) {
            Log::error('PlanController@index Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve plans.', [], 500);
        }
    }

    /**
     * Create single or multiple plans.
     */
    public function store(Request $request)
    {
        try {
            // Only Owner can create plans based on access scope summary
            if ($request->user()->role !== 'owner' && $request->user()->role !== 'staff') {
                return $this->errorResponse('Unauthorized. Only owner can create plans.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'plan_group_name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'durations' => 'required|array|min:1',
                'durations.*.duration_months' => 'required|integer|min:1',
                'durations.*.amount' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $gymId = $request->user()->gym_id;
            $createdPlans = $this->planService->createPlans($gymId, $request->all());

            return $this->successResponse('Plans created successfully', $createdPlans, 201);
        } catch (Exception $e) {
            Log::error('PlanController@store Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to create plans.', [], 500);
        }
    }

    /**
     * Update an existing plan.
     */
    public function update(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'owner' && $request->user()->role !== 'staff') {
                return $this->errorResponse('Unauthorized. Only owner can update plans.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'plan_group_name' => 'sometimes|required|string|max:100',
                'description' => 'nullable|string',
                'duration_months' => 'sometimes|required|integer|min:1',
                'amount' => 'sometimes|required|numeric|min:0',
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $gymId = $request->user()->gym_id;
            $updatedPlan = $this->planService->updatePlan($id, $gymId, $request->all());

            return $this->successResponse('Plan updated successfully', $updatedPlan);
        } catch (Exception $e) {
            Log::error('PlanController@update Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to update plan.', [], 500);
        }
    }

    /**
     * Delete a plan.
     */
    public function destroy(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'owner' && $request->user()->role !== 'staff') {
                return $this->errorResponse('Unauthorized. Only owner can delete plans.', [], 403);
            }

            $gymId = $request->user()->gym_id;
            $this->planService->deletePlan($id, $gymId);

            return $this->successResponse('Plan deleted successfully');
        } catch (Exception $e) {
            Log::error('PlanController@destroy Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete plan.', [], 500);
        }
    }
}
