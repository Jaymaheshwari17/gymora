<?php

namespace App\Services;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Register a new Owner and their Gym.
     */
    public function registerOwner(array $data)
    {
        try {
            // Generate unique gym code (e.g. GYM1001)
            $lastGym = Gym::latest('id')->first();
            $nextId = $lastGym ? $lastGym->id + 1 : 1001;
            $gymCode = 'GYM' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            // Create the Gym (without owner_id initially)
            $gym = Gym::create([
                'name' => $data['gym_name'],
                'gym_code' => $gymCode,
            ]);

            // Create the User (Owner)
            $user = User::create([
                'gym_id' => $gym->id,
                'role' => 'owner',
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'password' => Hash::make($data['password']),
                'status' => 'active',
            ]);

            // Update Gym with owner_id
            $gym->update(['owner_id' => $user->id]);

            // Generate Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user->load('gym'),
                'token' => $token,
            ];
        } catch (Exception $e) {
            Log::error('AuthService@registerOwner Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Authenticate a user and generate a token.
     */
    public function login(array $credentials)
    {
        try {
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return null; // Invalid credentials
            }

            if ($user->status !== 'active') {
                throw new Exception("Account is inactive.");
            }

            if (isset($credentials['login_role'])) {
                $expectedRole = $credentials['login_role'];
                if ($expectedRole === 'staff') {
                    // Staff/Trainer tab allows both roles
                    if ($user->role !== 'staff' && $user->role !== 'trainer') {
                        throw new Exception("Please use the correct login tab for your role.");
                    }
                } else {
                    if ($user->role !== $expectedRole) {
                        throw new Exception("Please use the correct login tab for your role.");
                    }
                }
            }
            
            if (isset($credentials['push_token']) && $credentials['push_token']) {
                $user->push_token = $credentials['push_token'];
                $user->save();
            }

            // Determine expiration: 30 days if remember_me is true, otherwise 12 hours
            $remember = isset($credentials['remember_me']) && ($credentials['remember_me'] == true || $credentials['remember_me'] == 'true');
            $expiresAt = $remember ? now()->addDays(30) : now()->addHours(12);

            $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

            return [
                'user' => $user->load('gym'),
                'token' => $token,
            ];
        } catch (Exception $e) {
            Log::error('AuthService@login Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
