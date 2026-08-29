<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Exception;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    use ApiResponse;

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100',
                'email' => [
                    'sometimes', 'required', 'email',
                    Rule::unique('users')->ignore($user->id)
                ],
                'mobile' => [
                    'sometimes', 'required', 'string', 'size:10',
                    Rule::unique('users')->ignore($user->id)
                ],
                'gender' => 'nullable|in:male,female,other',
                'dob' => 'nullable|date',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $data = $request->only('name', 'email', 'mobile', 'gender', 'dob');

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $gymId = $user->gym_id;
                $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs("profile_photos/gym_{$gymId}", $filename, 'public');
                $data['photo'] = 'storage/' . $path;
            }

            $user->update($data);

            return $this->successResponse('Profile updated successfully', $user);
        } catch (Exception $e) {
            Log::error('SettingsController@updateProfile Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to update profile.', [], 500);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => [
                    'required', 'string', 'confirmed',
                    Password::min(8)->letters()->numbers()->symbols()
                ],
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $user = $request->user();

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->errorResponse('Current password does not match.', [], 400);
            }

            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return $this->successResponse('Password changed successfully');
        } catch (Exception $e) {
            Log::error('SettingsController@changePassword Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to change password.', [], 500);
        }
    }
}
