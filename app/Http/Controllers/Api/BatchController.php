<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Exception;

class BatchController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the batches for the logged-in user's gym.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $batches = Batch::where('gym_id', $user->gym_id)->latest()->get();
            return $this->successResponse('Batches retrieved successfully', $batches);
        } catch (Exception $e) {
            Log::error('BatchController@index Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve batches.', [], 500);
        }
    }

    /**
     * Store a newly created batch.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $user = $request->user();
            
            $batch = Batch::create([
                'gym_id' => $user->gym_id,
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return $this->successResponse('Batch created successfully', $batch, 201);
        } catch (Exception $e) {
            Log::error('BatchController@store Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to create batch.', [], 500);
        }
    }

    /**
     * Update the specified batch.
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();
            $batch = Batch::where('gym_id', $user->gym_id)->where('id', $id)->first();

            if (!$batch) {
                return $this->errorResponse('Batch not found', [], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50',
                'start_time' => 'nullable|date_format:H:i',
                'end_time' => 'nullable|date_format:H:i|after:start_time',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $batch->update([
                'name' => $request->name,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return $this->successResponse('Batch updated successfully', $batch);
        } catch (Exception $e) {
            Log::error('BatchController@update Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to update batch.', [], 500);
        }
    }

    /**
     * Remove the specified batch.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $batch = Batch::where('gym_id', $user->gym_id)->where('id', $id)->first();

            if (!$batch) {
                return $this->errorResponse('Batch not found', [], 404);
            }

            $batch->delete();
            return $this->successResponse('Batch deleted successfully');
        } catch (Exception $e) {
            Log::error('BatchController@destroy Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete batch.', [], 500);
        }
    }
}
