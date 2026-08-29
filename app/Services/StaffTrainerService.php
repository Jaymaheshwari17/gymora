<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StaffTrainerService
{
    /**
     * Get list of staff and trainers for a specific gym.
     */
    public function getStaffAndTrainers(int $gymId)
    {
        try {
            return User::where('gym_id', $gymId)
                ->whereIn('role', ['staff', 'trainer'])
                ->latest()
                ->get();
        } catch (Exception $e) {
            Log::error('StaffTrainerService@getStaffAndTrainers Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add a new staff or trainer.
     */
    public function createStaffTrainer(int $gymId, array $data)
    {
        try {
            return User::create([
                'gym_id' => $gymId,
                'role' => $data['role'], // 'staff' or 'trainer'
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'password' => Hash::make($data['password']),
                'address' => $data['address'] ?? null,
                'photo' => $data['photo'] ?? null,
                'gender' => $data['gender'] ?? null,
                'dob' => $data['dob'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'status' => 'active',
            ]);
        } catch (Exception $e) {
            Log::error('StaffTrainerService@createStaffTrainer Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing staff or trainer.
     */
    public function updateStaffTrainer(int $userId, int $gymId, array $data)
    {
        try {
            $user = User::where('id', $userId)
                ->where('gym_id', $gymId)
                ->whereIn('role', ['staff', 'trainer'])
                ->firstOrFail();

            $updateData = [
                'role' => $data['role'] ?? $user->role,
                'name' => $data['name'] ?? $user->name,
                'mobile' => $data['mobile'] ?? $user->mobile,
                'address' => $data['address'] ?? $user->address,
                'photo' => $data['photo'] ?? $user->photo,
                'gender' => $data['gender'] ?? $user->gender,
                'dob' => $data['dob'] ?? $user->dob,
                'status' => $data['status'] ?? $user->status,
            ];

            // Only update email if provided and different (would need unique validation in controller)
            if (isset($data['email'])) {
                $updateData['email'] = $data['email'];
            }

            // Only update password if provided
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            // Update trainer specific fields
            if (isset($data['role']) && $data['role'] === 'trainer') {
                $updateData['specialization'] = $data['specialization'] ?? $user->specialization;
                $updateData['experience_years'] = $data['experience_years'] ?? $user->experience_years;
            } else if (isset($data['role']) && $data['role'] === 'staff') {
                $updateData['specialization'] = null;
                $updateData['experience_years'] = null;
            }

            $user->update($updateData);

            return $user;
        } catch (Exception $e) {
            Log::error('StaffTrainerService@updateStaffTrainer Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a staff or trainer.
     */
    public function deleteStaffTrainer(int $userId, int $gymId)
    {
        try {
            $user = User::where('id', $userId)
                ->where('gym_id', $gymId)
                ->whereIn('role', ['staff', 'trainer'])
                ->firstOrFail();
            
            $user->delete();
            return true;
        } catch (Exception $e) {
            Log::error('StaffTrainerService@deleteStaffTrainer Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
