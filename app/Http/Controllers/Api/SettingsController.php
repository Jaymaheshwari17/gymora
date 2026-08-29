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
    public function getGymProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'owner' || !$user->gym_id) {
                return $this->errorResponse('Unauthorized', [], 403);
            }

            $gym = \App\Models\Gym::find($user->gym_id);
            if (!$gym) {
                return $this->errorResponse('Gym not found', [], 404);
            }

            return $this->successResponse('Gym profile retrieved', [
                'gym_name' => $gym->name,
                'owner_name' => $user->name,
                'contact_number' => $gym->contact_number ?? $user->mobile,
                'email' => $user->email,
                'address' => $gym->address,
                'gst_number' => $gym->gst_number,
                'instagram_link' => $gym->instagram_link,
                'facebook_link' => $gym->facebook_link,
                'logo_url' => $gym->logo ? url($gym->logo) : null
            ]);
        } catch (Exception $e) {
            Log::error('SettingsController@getGymProfile: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve profile', [], 500);
        }
    }

    public function updateGymProfile(Request $request)
    {
        try {
            $user = $request->user();
            
            if ($user->role !== 'owner' || !$user->gym_id) {
                return $this->errorResponse('Unauthorized', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'gym_name' => 'required|string|max:150',
                'owner_name' => 'required|string|max:100',
                'contact_number' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'gst_number' => 'nullable|string|max:50',
                'instagram_link' => 'nullable|string|max:255',
                'facebook_link' => 'nullable|string|max:255',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $gym = \App\Models\Gym::find($user->gym_id);
            
            $gym->name = $request->input('gym_name');
            $gym->contact_number = $request->input('contact_number');
            $gym->address = $request->input('address');
            $gym->gst_number = $request->input('gst_number');
            $gym->instagram_link = $request->input('instagram_link');
            $gym->facebook_link = $request->input('facebook_link');

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = 'gym_' . $gym->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/gyms'), $filename);
                $gym->logo = 'uploads/gyms/' . $filename;
            }

            $gym->save();

            if ($user->name !== $request->input('owner_name')) {
                $user->name = $request->input('owner_name');
                $user->save();
            }

            return $this->successResponse('Gym Profile updated successfully', [
                'gym_name' => $gym->name,
                'owner_name' => $user->name,
                'logo_url' => $gym->logo ? url($gym->logo) : null
            ]);

        } catch (Exception $e) {
            Log::error('SettingsController@updateGymProfile: ' . $e->getMessage());
            return $this->errorResponse('Failed to update gym profile', [], 500);
        }
    }
}
