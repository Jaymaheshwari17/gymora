<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Exception;

class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new owner and their gym.
     */
    public function registerOwner(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|regex:/^[a-zA-Z\s]+$/',
                'email' => 'required|string|email|max:150|unique:users',
                'mobile' => 'required|string|size:10|regex:/^[0-9]+$/|unique:users',
                'password' => [
                    'required',
                    'string',
                    'confirmed',
                    Password::min(8)
                        ->letters()
                        ->numbers()
                        ->symbols() // Requires at least one special character
                ],
                'gym_name' => 'required|string|max:150',
                'terms' => 'accepted',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $result = $this->authService->registerOwner($request->all());

            return $this->successResponse('Registration successful', $result, 201);

        } catch (Exception $e) {
            Log::error('AuthController@registerOwner Exception: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong during registration.', [], 500);
        }
    }

    /**
     * Shared login for all roles.
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
                'login_role' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $result = $this->authService->login($request->only('email', 'password', 'remember_me', 'login_role', 'push_token'));

            if (!$result) {
                return $this->errorResponse('Invalid credentials', [], 401);
            }

            return $this->successResponse('Login successful', $result, 200);

        } catch (Exception $e) {
            Log::error('AuthController@login Exception: ' . $e->getMessage());
            
            if ($e->getMessage() === 'Account is inactive.' || $e->getMessage() === 'Please use the correct login tab for your role.') {
                return $this->errorResponse($e->getMessage(), [], 403);
            }
            
            return $this->errorResponse('Something went wrong during login.', [], 500);
        }
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->successResponse('Logged out successfully');
        } catch (Exception $e) {
            Log::error('AuthController@logout Exception: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong during logout.', [], 500);
        }
    }

    /**
     * Send OTP for Forgot Password
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation Error', $validator->errors(), 422);
            }

            $email = $request->email;
            // Generate 6 digit OTP
            $otp = rand(100000, 999999);

            // Store in DB
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => $otp, // Note: In production this should be hashed, but for simplicity of OTP verification we keep it plain here, or hash it and compare. We will keep plain for this simple OTP.
                    'created_at' => now()
                ]
            );

            // Send Email logic goes here...
            // For now, returning it in response for testing/development
            return $this->successResponse('OTP sent successfully to your email.', ['otp' => $otp]);

        } catch (Exception $e) {
            Log::error('AuthController@forgotPassword Exception: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong.', [], 500);
        }
    }

    /**
     * Reset Password using OTP
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|string|size:6',
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

            $email = $request->email;
            $otp = $request->otp;

            $resetRecord = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->first();

            if (!$resetRecord || $resetRecord->token !== $otp) {
                return $this->errorResponse('Invalid or expired OTP.', [], 400);
            }

            // Check if expired (e.g. 15 minutes)
            if (\Carbon\Carbon::parse($resetRecord->created_at)->addMinutes(15)->isPast()) {
                \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->delete();
                return $this->errorResponse('OTP has expired. Please request a new one.', [], 400);
            }

            // Update user password
            $user = \App\Models\User::where('email', $email)->first();
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
            $user->save();

            // Delete token
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->delete();

            return $this->successResponse('Password reset successfully. You can now login.');

        } catch (Exception $e) {
            Log::error('AuthController@resetPassword Exception: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong during reset.', [], 500);
        }
    }
}
