<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StaffTrainerService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Exception;
use Illuminate\Validation\Rule;

class StaffTrainerController extends Controller
{
    use ApiResponse;

    protected $staffTrainerService;

    public function __construct(StaffTrainerService $staffTrainerService)
    {
        $this->staffTrainerService = $staffTrainerService;
    }

    /**
     * Get list of staff and trainers.
     */
    public function index(Request $request)
    {
        try {
            // Owner can see all staff/trainers in their gym
            if ($request->user()->role !== 'owner' && $request->user()->role !== 'staff') {
                return $this->errorResponse('Unauthorized access.', [], 403);
            }

            $gymId = $request->user()->gym_id;
            $staff = $this->staffTrainerService->getStaffAndTrainers($gymId);
            
            return $this->successResponse('Staff and trainers retrieved successfully', $staff);
        } catch (Exception $e) {
            Log::error('StaffTrainerController@index Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve staff and trainers.', [], 500);
        }
    }

    /**
     * Add a new staff or trainer.
     */
    public function store(Request $request)
    {
        try {
            if ($request->user()->role !== 'owner' && $request->user()->role !== 'staff') {
                return $this->errorResponse('Unauthorized. Only owner or staff can add.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'role' => 'required|in:staff,trainer',
                'name' => 'required|string|max:100',
                'email' => 'required|string|email|max:150|unique:users',
                'mobile' => 'required|string|size:10|regex:/^[0-9]+$/|unique:users',
                'address' => 'nullable|string',
                'gender' => 'nullable|in:male,female,other',
                'dob' => 'nullable|date',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'specialization' => 'required_if:role,trainer|nullable|string|max:100',
                'experience_years' => 'required_if:role,trainer|nullable|integer|min:0',
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    Password::min(8)->letters()->numbers()->symbols()
                ],
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $gymId = $request->user()->gym_id;
            $data = $request->all();

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs("profile_photos/gym_{$gymId}", $filename, 'public');
                $data['photo'] = 'storage/' . $path;
            }

            $user = $this->staffTrainerService->createStaffTrainer($gymId, $data);

            return $this->successResponse(ucfirst($request->role) . ' created successfully', $user, 201);
        } catch (Exception $e) {
            Log::error('StaffTrainerController@store Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to create staff/trainer.', [], 500);
        }
    }

    /**
     * View specific staff or trainer details.
     */
    public function show(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'owner') {
                return $this->errorResponse('Unauthorized access.', [], 403);
            }

            $gymId = $request->user()->gym_id;
            $user = \App\Models\User::where('id', $id)
                ->where('gym_id', $gymId)
                ->whereIn('role', ['staff', 'trainer'])
                ->first();

            if (!$user) {
                return $this->errorResponse('Staff/Trainer not found', [], 404);
            }

            return $this->successResponse('Staff/Trainer details retrieved successfully', $user);
        } catch (Exception $e) {
            Log::error('StaffTrainerController@show Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve details.', [], 500);
        }
    }

    /**
     * Update an existing staff or trainer.
     */
    public function update(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'owner' && $request->user()->role !== 'staff') {
                return $this->errorResponse('Unauthorized. Only owner or staff can edit.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'role' => 'sometimes|required|in:staff,trainer',
                'name' => 'sometimes|required|string|max:100',
                'email' => [
                    'sometimes', 'required', 'string', 'email', 'max:150',
                    Rule::unique('users')->ignore($id),
                ],
                'mobile' => [
                    'sometimes', 'required', 'string', 'size:10', 'regex:/^[0-9]+$/',
                    Rule::unique('users')->ignore($id),
                ],
                'address' => 'nullable|string',
                'gender' => 'nullable|in:male,female,other',
                'dob' => 'nullable|date',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'specialization' => 'required_if:role,trainer|nullable|string|max:100',
                'experience_years' => 'required_if:role,trainer|nullable|integer|min:0',
                'status' => 'sometimes|required|in:active,inactive',
                'password' => [
                    'nullable',
                    'string',
                    'confirmed',
                    Password::min(8)->letters()->numbers()->symbols()
                ],
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $gymId = $request->user()->gym_id;
            $data = $request->all();

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs("profile_photos/gym_{$gymId}", $filename, 'public');
                $data['photo'] = 'storage/' . $path;
            }

            $updatedUser = $this->staffTrainerService->updateStaffTrainer($id, $gymId, $data);

            return $this->successResponse('Details updated successfully', $updatedUser);
        } catch (Exception $e) {
            Log::error('StaffTrainerController@update Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to update staff/trainer.', [], 500);
        }
    }

    /**
     * Delete a staff or trainer.
     */
    public function destroy(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'owner' && $request->user()->role !== 'staff') {
                return $this->errorResponse('Unauthorized. Only owner or staff can delete.', [], 403);
            }

            $gymId = $request->user()->gym_id;
            $this->staffTrainerService->deleteStaffTrainer($id, $gymId);

            return $this->successResponse('Staff/Trainer deleted successfully');
        } catch (Exception $e) {
            Log::error('StaffTrainerController@destroy Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete staff/trainer.', [], 500);
        }
    }
}
