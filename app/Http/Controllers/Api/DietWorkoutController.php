<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DietPlan;
use App\Models\DietPlanMeal;
use App\Models\Member;
use App\Models\MemberDietPlan;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class DietWorkoutController extends Controller
{
    use ApiResponse;

    protected $allowedRoles = ['owner', 'staff', 'trainer'];

    /**
     * List all diet plans for the gym
     */
    public function index(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;

            $plans = DietPlan::where('gym_id', $gymId)
                ->with(['meals', 'creator', 'assignments' => function($q) {
                    $q->where('status', 'active')->with('member.user');
                }])
                ->withCount(['meals', 'assignments' => function($q) {
                    $q->where('status', 'active');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($plan) {
                    $activeMembers = $plan->assignments->map(fn($a) => [
                        'member_name' => $a->member->user->name ?? 'Unknown',
                    ]);
                    return [
                        'id' => $plan->id,
                        'title' => $plan->title,
                        'is_template' => $plan->is_template,
                        'created_by_name' => $plan->creator->name ?? 'Unknown',
                        'created_by_role' => ucfirst($plan->creator->role ?? 'owner'),
                        'meals_count' => $plan->meals_count,
                        'active_assignments' => $plan->assignments_count,
                        'assigned_members' => $activeMembers,
                        'meals' => $plan->meals->map(fn($m) => [
                            'id' => $m->id,
                            'meal_type' => $m->meal_type,
                            'food_items' => $m->food_items,
                            'calories' => $m->calories,
                        ]),
                        'created_at' => $plan->created_at,
                    ];
                });

            return $this->successResponse('Diet plans fetched', $plans);
        } catch (Exception $e) {
            Log::error('DietWorkoutController@index: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch diet plans.', [], 500);
        }
    }

    /**
     * Create a new diet plan with meals
     */
    public function store(Request $request)
    {
        try {
            if (!in_array($request->user()->role, $this->allowedRoles)) {
                return $this->errorResponse('Unauthorized.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:150',
                'meals' => 'required|array|min:1',
                'meals.*.meal_type' => 'required|in:breakfast,mid_morning,lunch,evening,dinner',
                'meals.*.food_items' => 'required|string',
                'meals.*.calories' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            DB::beginTransaction();
            $user = $request->user();

            $plan = DietPlan::create([
                'gym_id' => $user->gym_id,
                'title' => $request->title,
                'created_by' => $user->id,
                'is_template' => $request->boolean('is_template', true),
            ]);

            foreach ($request->meals as $meal) {
                DietPlanMeal::create([
                    'diet_plan_id' => $plan->id,
                    'meal_type' => $meal['meal_type'],
                    'food_items' => $meal['food_items'],
                    'calories' => $meal['calories'] ?? null,
                ]);
            }

            DB::commit();
            return $this->successResponse('Diet plan created successfully.', $plan->load('meals'), 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DietWorkoutController@store: ' . $e->getMessage());
            return $this->errorResponse('Failed to create diet plan.', [], 500);
        }
    }

    /**
     * Update an existing diet plan and its meals
     */
    public function update(Request $request, $id)
    {
        try {
            $gymId = $request->user()->gym_id;
            $plan = DietPlan::where('gym_id', $gymId)->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:150',
                'meals' => 'required|array|min:1',
                'meals.*.meal_type' => 'required|in:breakfast,mid_morning,lunch,evening,dinner',
                'meals.*.food_items' => 'required|string',
                'meals.*.calories' => 'nullable|integer|min:0',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            DB::beginTransaction();

            $plan->update(['title' => $request->title]);

            // Delete old meals, re-create
            DietPlanMeal::where('diet_plan_id', $plan->id)->delete();
            foreach ($request->meals as $meal) {
                DietPlanMeal::create([
                    'diet_plan_id' => $plan->id,
                    'meal_type' => $meal['meal_type'],
                    'food_items' => $meal['food_items'],
                    'calories' => $meal['calories'] ?? null,
                ]);
            }

            DB::commit();
            return $this->successResponse('Diet plan updated successfully.', $plan->load('meals'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DietWorkoutController@update: ' . $e->getMessage());
            return $this->errorResponse('Failed to update diet plan.', [], 500);
        }
    }

    /**
     * Delete a diet plan (only if no active assignments)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $gymId = $request->user()->gym_id;
            $plan = DietPlan::where('gym_id', $gymId)->findOrFail($id);

            $activeAssignments = MemberDietPlan::where('diet_plan_id', $id)->where('status', 'active')->count();
            if ($activeAssignments > 0) {
                return $this->errorResponse('Cannot delete: this plan is currently assigned to ' . $activeAssignments . ' member(s).', [], 422);
            }

            DietPlanMeal::where('diet_plan_id', $id)->delete();
            $plan->delete();

            return $this->successResponse('Diet plan deleted successfully.');
        } catch (Exception $e) {
            Log::error('DietWorkoutController@destroy: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete diet plan.', [], 500);
        }
    }

    /**
     * Assign a diet plan to a member
     */
    public function assign(Request $request, $id)
    {
        try {
            $gymId = $request->user()->gym_id;
            DietPlan::where('gym_id', $gymId)->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'member_id' => 'required|exists:members,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            // Deactivate any existing active assignment for this member
            MemberDietPlan::where('member_id', $request->member_id)
                ->where('status', 'active')
                ->update(['status' => 'completed']);

            $assignment = MemberDietPlan::create([
                'member_id' => $request->member_id,
                'diet_plan_id' => $id,
                'assigned_by' => $request->user()->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date ?? null,
                'status' => 'active',
            ]);

            return $this->successResponse('Diet plan assigned successfully.', $assignment, 201);
        } catch (Exception $e) {
            Log::error('DietWorkoutController@assign: ' . $e->getMessage());
            return $this->errorResponse('Failed to assign diet plan.', [], 500);
        }
    }

    /**
     * Get members list for assignment dropdown
     */
    public function getMembers(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;
            $members = Member::with('user')
                ->where('gym_id', $gymId)
                ->where('status', 'active')
                ->get()
                ->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->user->name ?? 'Unknown',
                    'photo_url' => 'https://ui-avatars.com/api/?name=' . urlencode($m->user->name ?? 'U') . '&background=f3f4f6&color=374151',
                ]);

            return $this->successResponse('Members fetched', $members);
        } catch (Exception $e) {
            Log::error('DietWorkoutController@getMembers: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch members.', [], 500);
        }
    }

    /**
     * Duplicate a diet plan (clone it with all meals)
     */
    public function duplicate(Request $request, $id)
    {
        try {
            $gymId = $request->user()->gym_id;
            $original = DietPlan::where('gym_id', $gymId)->with('meals')->findOrFail($id);

            DB::beginTransaction();

            $copy = DietPlan::create([
                'gym_id'      => $gymId,
                'title'       => $request->input('title', 'Copy of ' . $original->title),
                'created_by'  => $request->user()->id,
                'is_template' => $original->is_template,
            ]);

            foreach ($original->meals as $meal) {
                DietPlanMeal::create([
                    'diet_plan_id' => $copy->id,
                    'meal_type'    => $meal->meal_type,
                    'food_items'   => $meal->food_items,
                    'calories'     => $meal->calories,
                ]);
            }

            DB::commit();
            return $this->successResponse('Diet plan duplicated successfully.', $copy->load('meals'), 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DietWorkoutController@duplicate: ' . $e->getMessage());
            return $this->errorResponse('Failed to duplicate diet plan.', [], 500);
        }
    }
    // ==========================================
    // WORKOUT PLANS
    // ==========================================

    public function indexWorkouts(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;

            $plans = \App\Models\WorkoutPlan::where('gym_id', $gymId)
                ->with(['days', 'creator', 'assignments' => function($q) {
                    $q->where('status', 'active')->with('member.user');
                }])
                ->withCount(['days', 'assignments' => function($q) {
                    $q->where('status', 'active');
                }])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($plan) {
                    $activeMembers = $plan->assignments->map(fn($a) => [
                        'member_name' => $a->member->user->name ?? 'Unknown',
                    ]);
                    return [
                        'id' => $plan->id,
                        'title' => $plan->title,
                        'is_template' => $plan->is_template,
                        'created_by_name' => $plan->creator->name ?? 'Unknown',
                        'created_by_role' => ucfirst($plan->creator->role ?? 'owner'),
                        'days_count' => $plan->days_count,
                        'active_assignments' => $plan->assignments_count,
                        'assigned_members' => $activeMembers,
                        'days' => $plan->days->map(fn($d) => [
                            'id' => $d->id,
                            'day_label' => $d->day_label,
                            'exercises' => is_string($d->exercises) ? json_decode($d->exercises, true) : $d->exercises,
                        ]),
                        'created_at' => $plan->created_at,
                    ];
                });

            return $this->successResponse('Workout plans fetched', $plans);
        } catch (Exception $e) {
            Log::error('DietWorkoutController@indexWorkouts: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch workout plans.', [], 500);
        }
    }

    public function storeWorkout(Request $request)
    {
        try {
            if (!in_array($request->user()->role, $this->allowedRoles)) {
                return $this->errorResponse('Unauthorized.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:150',
                'days' => 'required|array|min:1',
                'days.*.day_label' => 'required|string',
                'days.*.exercises' => 'required|array',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            DB::beginTransaction();
            $user = $request->user();

            $plan = \App\Models\WorkoutPlan::create([
                'gym_id' => $user->gym_id,
                'title' => $request->title,
                'created_by' => $user->id,
                'is_template' => $request->boolean('is_template', true),
            ]);

            foreach ($request->days as $day) {
                \App\Models\WorkoutPlanDay::create([
                    'workout_plan_id' => $plan->id,
                    'day_label' => $day['day_label'],
                    'exercises' => json_encode($day['exercises']),
                ]);
            }

            DB::commit();
            return $this->successResponse('Workout plan created successfully.', $plan->load('days'), 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DietWorkoutController@storeWorkout: ' . $e->getMessage());
            return $this->errorResponse('Failed to create workout plan.', [], 500);
        }
    }

    public function updateWorkout(Request $request, $id)
    {
        try {
            $gymId = $request->user()->gym_id;
            $plan = \App\Models\WorkoutPlan::where('gym_id', $gymId)->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:150',
                'days' => 'required|array|min:1',
                'days.*.day_label' => 'required|string',
                'days.*.exercises' => 'required|array',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            DB::beginTransaction();

            $plan->update(['title' => $request->title]);

            \App\Models\WorkoutPlanDay::where('workout_plan_id', $plan->id)->delete();
            foreach ($request->days as $day) {
                \App\Models\WorkoutPlanDay::create([
                    'workout_plan_id' => $plan->id,
                    'day_label' => $day['day_label'],
                    'exercises' => json_encode($day['exercises']),
                ]);
            }

            DB::commit();
            return $this->successResponse('Workout plan updated successfully.', $plan->load('days'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DietWorkoutController@updateWorkout: ' . $e->getMessage());
            return $this->errorResponse('Failed to update workout plan.', [], 500);
        }
    }

    public function destroyWorkout(Request $request, $id)
    {
        try {
            $gymId = $request->user()->gym_id;
            $plan = \App\Models\WorkoutPlan::where('gym_id', $gymId)->findOrFail($id);

            $activeAssignments = \App\Models\MemberWorkoutPlan::where('workout_plan_id', $id)->where('status', 'active')->count();
            if ($activeAssignments > 0) {
                return $this->errorResponse('Cannot delete: this plan is currently assigned to ' . $activeAssignments . ' member(s).', [], 422);
            }

            \App\Models\WorkoutPlanDay::where('workout_plan_id', $id)->delete();
            $plan->delete();

            return $this->successResponse('Workout plan deleted successfully.');
        } catch (Exception $e) {
            Log::error('DietWorkoutController@destroyWorkout: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete workout plan.', [], 500);
        }
    }

    public function assignWorkout(Request $request, $id)
    {
        try {
            $gymId = $request->user()->gym_id;
            \App\Models\WorkoutPlan::where('gym_id', $gymId)->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'member_id' => 'required|exists:members,id',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            \App\Models\MemberWorkoutPlan::where('member_id', $request->member_id)
                ->where('status', 'active')
                ->update(['status' => 'completed']);

            $assignment = \App\Models\MemberWorkoutPlan::create([
                'member_id' => $request->member_id,
                'workout_plan_id' => $id,
                'assigned_by' => $request->user()->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date ?? null,
                'status' => 'active',
            ]);

            return $this->successResponse('Workout plan assigned successfully.', $assignment, 201);
        } catch (Exception $e) {
            Log::error('DietWorkoutController@assignWorkout: ' . $e->getMessage());
            return $this->errorResponse('Failed to assign workout plan.', [], 500);
        }
    }

    public function duplicateWorkout(Request $request, $id)
    {
        try {
            $gymId = $request->user()->gym_id;
            $original = \App\Models\WorkoutPlan::where('gym_id', $gymId)->with('days')->findOrFail($id);

            DB::beginTransaction();

            $copy = \App\Models\WorkoutPlan::create([
                'gym_id'      => $gymId,
                'title'       => $request->input('title', 'Copy of ' . $original->title),
                'created_by'  => $request->user()->id,
                'is_template' => $original->is_template,
            ]);

            foreach ($original->days as $day) {
                \App\Models\WorkoutPlanDay::create([
                    'workout_plan_id' => $copy->id,
                    'day_label'       => $day->day_label,
                    'exercises'       => $day->exercises,
                ]);
            }

            DB::commit();
            return $this->successResponse('Workout plan duplicated successfully.', $copy->load('days'), 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('DietWorkoutController@duplicateWorkout: ' . $e->getMessage());
            return $this->errorResponse('Failed to duplicate workout plan.', [], 500);
        }
    }
}
