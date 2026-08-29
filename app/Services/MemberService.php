<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class MemberService
{
    /**
     * Get all members for a gym.
     */
    public function getMembers(int $gymId, ?int $trainerId = null)
    {
        try {
            $query = Member::with(['user', 'plan', 'trainer', 'batch', 'payments'])->where('gym_id', $gymId);
            
            // If trainer is logged in, show only their assigned members
            if ($trainerId) {
                $query->where('trainer_id', $trainerId);
            }
            
            return $query->latest()->get();
        } catch (Exception $e) {
            Log::error('MemberService@getMembers Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add a new member.
     */
    public function createMember(int $gymId, array $data)
    {
        DB::beginTransaction();
        try {
            // Get Plan details
            $plan = Plan::where('id', $data['plan_id'])->where('gym_id', $gymId)->firstOrFail();
            $planAmount = $plan->amount;
            $discount = $data['discount'] ?? 0;
            $totalAmount = $planAmount - $discount;
            $paidAmount = $data['amount_received'] ?? 0;
            $dueAmount = $totalAmount - $paidAmount;

            // Determine payment status
            $paymentStatus = 'pending';
            if ($dueAmount <= 0) $paymentStatus = 'paid';
            else if ($paidAmount > 0) $paymentStatus = 'partial';

            // Create User account for member
            $user = User::create([
                'gym_id' => $gymId,
                'role' => 'member',
                'name' => $data['name'],
                'email' => $data['email'],
                'mobile' => $data['mobile'],
                'password' => Hash::make($data['password']),
                'address' => $data['address'] ?? null,
                'photo' => $data['photo'] ?? null,
                'gender' => $data['gender'] ?? null,
                'dob' => $data['dob'] ?? null,
                'status' => 'active',
            ]);

            // Create Member profile
            $member = Member::create([
                'user_id' => $user->id,
                'gym_id' => $gymId,
                'batch_id' => $data['batch_id'] ?? null,
                'trainer_id' => $data['trainer_id'] ?? null,
                'plan_id' => $plan->id,
                'joining_date' => $data['joining_date'],
                'plan_amount' => $planAmount,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'status' => 'active',
            ]);

            // Create Payment record
            Payment::create([
                'member_id' => $member->id,
                'gym_id' => $gymId,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_date' => $paidAmount > 0 ? now()->toDateString() : null,
                'status' => $paymentStatus,
            ]);

            DB::commit();
            return $member->load(['user', 'plan', 'trainer', 'payments']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('MemberService@createMember Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing member.
     */
    public function updateMember(int $memberId, int $gymId, array $data)
    {
        DB::beginTransaction();
        try {
            $member = Member::where('id', $memberId)->where('gym_id', $gymId)->firstOrFail();
            $user = $member->user;

            // Update user details
            $userUpdate = [];
            if (isset($data['name'])) $userUpdate['name'] = $data['name'];
            if (isset($data['email'])) $userUpdate['email'] = $data['email'];
            if (isset($data['mobile'])) $userUpdate['mobile'] = $data['mobile'];
            if (isset($data['address'])) $userUpdate['address'] = $data['address'];
            if (isset($data['photo'])) $userUpdate['photo'] = $data['photo'];
            if (isset($data['gender'])) $userUpdate['gender'] = $data['gender'];
            if (isset($data['dob'])) $userUpdate['dob'] = $data['dob'];
            if (isset($data['status'])) $userUpdate['status'] = $data['status'];
            
            if (!empty($data['password'])) {
                $userUpdate['password'] = Hash::make($data['password']);
            }
            
            if (!empty($userUpdate)) {
                $user->update($userUpdate);
            }

            // Update member details
            $memberUpdate = [];
            if (isset($data['batch_id'])) $memberUpdate['batch_id'] = $data['batch_id'];
            if (isset($data['trainer_id'])) $memberUpdate['trainer_id'] = $data['trainer_id'];
            if (isset($data['status'])) $memberUpdate['status'] = $data['status'];
            if (isset($data['joining_date'])) $memberUpdate['joining_date'] = $data['joining_date'];

            // Handle Plan and Payment Updates if passed
            if (isset($data['plan_id'])) {
                $plan = Plan::where('id', $data['plan_id'])->where('gym_id', $gymId)->first();
                if ($plan) {
                    $memberUpdate['plan_id'] = $plan->id;
                    $planAmount = $plan->amount;
                    $discount = isset($data['discount']) ? (float)$data['discount'] : $member->discount;
                    $totalAmount = max(0, $planAmount - $discount);
                    
                    $memberUpdate['plan_amount'] = $planAmount;
                    $memberUpdate['discount'] = $discount;
                    $memberUpdate['total_amount'] = $totalAmount;
                    
                    // Handle Payment update
                    if (isset($data['amount_received'])) {
                        $paidAmount = (float)$data['amount_received'];
                        $dueAmount = max(0, $totalAmount - $paidAmount);
                        
                        $paymentStatus = 'pending';
                        if ($dueAmount <= 0) $paymentStatus = 'paid';
                        else if ($paidAmount > 0) $paymentStatus = 'partial';
                        
                        // Find the latest payment record or create one
                        $payment = Payment::where('member_id', $member->id)->orderBy('id', 'desc')->first();
                        
                        if ($payment) {
                            $payment->update([
                                'total_amount' => $totalAmount,
                                'paid_amount' => $paidAmount,
                                'due_amount' => $dueAmount,
                                'status' => $paymentStatus,
                            ]);
                        } else {
                            Payment::create([
                                'member_id' => $member->id,
                                'gym_id' => $gymId,
                                'total_amount' => $totalAmount,
                                'paid_amount' => $paidAmount,
                                'due_amount' => $dueAmount,
                                'payment_date' => now()->toDateString(),
                                'status' => $paymentStatus,
                            ]);
                        }
                    }
                }
            }
            
            if (!empty($memberUpdate)) {
                $member->update($memberUpdate);
            }

            DB::commit();
            return $member->load(['user', 'plan', 'trainer', 'payments']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('MemberService@updateMember Error: ' . $e->getMessage());
            throw $e;
        }
    }
    public function deleteMember(int $id, int $gymId)
    {
        DB::beginTransaction();
        try {
            $member = Member::where('id', $id)->where('gym_id', $gymId)->firstOrFail();
            $user = User::findOrFail($member->user_id);
            $user->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('MemberService@deleteMember Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
