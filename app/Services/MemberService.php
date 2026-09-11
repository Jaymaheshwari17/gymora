<?php

namespace App\Services;

use App\Models\Member;
use App\Models\Payment;
use App\Models\PaymentTransaction;
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
            
            $members = $query->latest()->get();
            $now = now();
            $threeDaysFromNow = now()->addDays(3);
            $startOfMonth = now()->startOfMonth();

            foreach ($members as $m) {
                $plan = $m->plan;
                $duration = $plan ? (int)$plan->duration_months : 0;
                // Use plan_start_date for expiry calculation (joining_date is just the original registration date)
                $planStartDate = $m->plan_start_date 
                    ? \Carbon\Carbon::parse($m->plan_start_date) 
                    : ($m->joining_date ? \Carbon\Carbon::parse($m->joining_date) : null);
                $expiryDate = ($planStartDate && $duration > 0) ? $planStartDate->copy()->addMonths($duration) : null;

                $isExpired = $expiryDate ? $expiryDate->isPast() : false;
                $isExpiringSoon = $expiryDate ? ($expiryDate->between($now, $threeDaysFromNow)) : false;
                $isExpiredThisMonth = $expiryDate ? ($expiryDate->isPast() && $expiryDate->between($startOfMonth, $now)) : false;
                $daysRemaining = $expiryDate ? (int) $now->diffInDays($expiryDate, false) : 0;

                // New this month check - use original joining_date (registration date)
                $isNewThisMonth = false;
                $joiningDate = $m->joining_date ? \Carbon\Carbon::parse($m->joining_date) : null;
                if ($joiningDate) {
                    $isNewThisMonth = $joiningDate->isCurrentMonth() && $joiningDate->isCurrentYear();
                } elseif ($m->created_at) {
                    $isNewThisMonth = $m->created_at->isCurrentMonth() && $m->created_at->isCurrentYear();
                }

                $m->expiry_date = $expiryDate ? $expiryDate->format('Y-m-d') : null;
                $m->formatted_expiry_date = $expiryDate ? $expiryDate->format('d M Y') : 'No Expiry';
                $m->is_expired = $isExpired;
                $m->is_expiring_soon = $isExpiringSoon;
                $m->is_expired_this_month = $isExpiredThisMonth;
                $m->is_new_this_month = $isNewThisMonth;
                $m->days_remaining = $daysRemaining;

                if ($m->status === 'inactive') {
                    $m->dynamic_status = 'inactive';
                } elseif ($isExpired) {
                    $m->dynamic_status = 'expired';
                } elseif ($isExpiringSoon) {
                    $m->dynamic_status = 'expiring';
                } else {
                    $m->dynamic_status = 'active';
                }
            }

            return $members;
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
                'plan_start_date' => $data['joining_date'], // Initial plan starts on joining date
                'plan_amount' => $planAmount,
                'discount' => $discount,
                'total_amount' => $totalAmount,
                'status' => 'active',
            ]);

            // Create Payment record (with plan info snapshot so invoice always shows correct plan)
            $payment = Payment::create([
                'member_id'            => $member->id,
                'gym_id'               => $gymId,
                'plan_id'              => $plan->id,
                'plan_duration_months' => (int)$plan->duration_months,
                'plan_name'            => $plan->plan_group_name,
                'total_amount'         => $totalAmount,
                'paid_amount'          => $paidAmount,
                'due_amount'           => $dueAmount,
                'payment_date'         => $paidAmount > 0 ? ($data['joining_date'] ?? now()->toDateString()) : null,
                'status'               => $paymentStatus,
            ]);

            // If initial amount is paid, record transaction history
            if ($paidAmount > 0) {
                PaymentTransaction::create([
                    'payment_id' => $payment->id,
                    'member_id' => $member->id,
                    'gym_id' => $gymId,
                    'amount' => $paidAmount,
                    'payment_date' => $data['joining_date'] ?? now()->toDateString(),
                    'payment_mode' => $data['payment_mode'] ?? 'cash',
                    'notes' => 'Initial plan payment',
                ]);
            }

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
                                'plan_id' => $plan->id,
                                'plan_duration_months' => (int)$plan->duration_months,
                                'plan_name' => $plan->plan_group_name,
                                'total_amount' => $totalAmount,
                                'paid_amount' => $paidAmount,
                                'due_amount' => $dueAmount,
                                'status' => $paymentStatus,
                            ]);
                        } else {
                            Payment::create([
                                'member_id' => $member->id,
                                'gym_id' => $gymId,
                                'plan_id' => $plan->id,
                                'plan_duration_months' => (int)$plan->duration_months,
                                'plan_name' => $plan->plan_group_name,
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
    public function renewPlan(int $memberId, int $gymId, array $data)
    {
        DB::beginTransaction();
        try {
            $member = Member::where('id', $memberId)->where('gym_id', $gymId)->firstOrFail();
            $plan = Plan::where('id', $data['plan_id'])->where('gym_id', $gymId)->firstOrFail();
            
            $actionType = $data['action_type'] ?? 'renew'; // 'upgrade' (adjust previous payment) or 'renew' (new cycle payment)
            $planAmount = (float)$plan->amount;
            $discount = isset($data['discount']) ? (float)$data['discount'] : 0;
            $totalAmount = max(0, $planAmount - $discount);
            $paidAmount = isset($data['amount_received']) ? (float)$data['amount_received'] : 0;

            if ($actionType === 'upgrade') {
                // 🔁 PLAN CHANGE / UPGRADE (Adjusts previous payment & accounts for partial difference)
                $recentPayment = Payment::where('member_id', $member->id)
                    ->where('gym_id', $gymId)
                    ->latest('id')
                    ->first();

                $previousPaid = $recentPayment ? (float)$recentPayment->paid_amount : 0;
                $payingNow = isset($data['amount_received']) ? (float)$data['amount_received'] : 0;
                
                // Total cumulative paid for this plan = already paid + paying now
                $totalPaidCumulative = min($totalAmount, $previousPaid + $payingNow);
                $dueAmount = max(0, $totalAmount - $totalPaidCumulative);
                $paymentStatus = $dueAmount <= 0 ? 'paid' : ($totalPaidCumulative > 0 ? 'partial' : 'pending');

                if ($recentPayment) {
                    // Backfill initial transaction if missing
                    if ($previousPaid > 0 && $recentPayment->transactions()->count() === 0) {
                        PaymentTransaction::create([
                            'payment_id' => $recentPayment->id,
                            'member_id' => $member->id,
                            'gym_id' => $gymId,
                            'amount' => $previousPaid,
                            'payment_date' => $recentPayment->payment_date ?: ($recentPayment->created_at ? $recentPayment->created_at->toDateString() : now()->toDateString()),
                            'payment_mode' => 'cash',
                            'notes' => 'Initial plan payment'
                        ]);
                    }

                    $recentPayment->update([
                        'total_amount' => $totalAmount,
                        'paid_amount' => $totalPaidCumulative,
                        'due_amount' => $dueAmount,
                        'payment_date' => now()->toDateString(),
                        'status' => $paymentStatus,
                    ]);
                } else {
                    $recentPayment = Payment::create([
                        'member_id'            => $member->id,
                        'gym_id'               => $gymId,
                        'plan_id'              => $plan->id,
                        'plan_duration_months' => (int)$plan->duration_months,
                        'plan_name'            => $plan->plan_group_name,
                        'total_amount'         => $totalAmount,
                        'paid_amount'          => $totalPaidCumulative,
                        'due_amount'           => $dueAmount,
                        'payment_date'         => now()->toDateString(),
                        'status'               => $paymentStatus,
                    ]);
                }

                if ($payingNow > 0) {
                    PaymentTransaction::create([
                        'payment_id' => $recentPayment->id,
                        'member_id' => $member->id,
                        'gym_id' => $gymId,
                        'amount' => $payingNow,
                        'payment_date' => now()->toDateString(),
                        'payment_mode' => $data['payment_mode'] ?? 'cash',
                        'notes' => 'Plan Upgrade difference payment',
                    ]);
                }

                // Update Member profile
                // NOTE: joining_date is NEVER updated on renew/upgrade - it's the original gym joining date
                $member->update([
                    'plan_id' => $plan->id,
                    'plan_start_date' => $data['start_date'] ?? now()->toDateString(),
                    'plan_amount' => $planAmount,
                    'discount' => $discount,
                    'total_amount' => $totalAmount,
                    'status' => 'active',
                ]);
            } else {
                // 🔄 NORMAL NEXT-CYCLE RENEWAL (Creates distinct payment for next period)
                $dueAmount = max(0, $totalAmount - $paidAmount);
                $paymentStatus = 'pending';
                if ($dueAmount <= 0) $paymentStatus = 'paid';
                else if ($paidAmount > 0) $paymentStatus = 'partial';

                // NOTE: joining_date is NEVER updated on renew - it's the original gym joining date
                $member->update([
                    'plan_id' => $plan->id,
                    'plan_start_date' => $data['start_date'] ?? now()->toDateString(),
                    'plan_amount' => $planAmount,
                    'discount' => $discount,
                    'total_amount' => $totalAmount,
                    'status' => 'active',
                ]);

                // Create new payment record for this renewal cycle (with plan snapshot)
                $newPayment = Payment::create([
                    'member_id'            => $member->id,
                    'gym_id'               => $gymId,
                    'plan_id'              => $plan->id,
                    'plan_duration_months' => (int)$plan->duration_months,
                    'plan_name'            => $plan->plan_group_name,
                    'total_amount'         => $totalAmount,
                    'paid_amount'          => $paidAmount,
                    'due_amount'           => $dueAmount,
                    'payment_date'         => $paidAmount > 0 ? ($data['start_date'] ?? now()->toDateString()) : null,
                    'status'               => $paymentStatus,
                ]);

                if ($paidAmount > 0) {
                    PaymentTransaction::create([
                        'payment_id' => $newPayment->id,
                        'member_id' => $member->id,
                        'gym_id' => $gymId,
                        'amount' => $paidAmount,
                        'payment_date' => $data['start_date'] ?? now()->toDateString(),
                        'payment_mode' => $data['payment_mode'] ?? 'cash',
                        'notes' => 'Renewal payment',
                    ]);
                }
            }

            DB::commit();
            return $member->load(['user', 'plan', 'trainer', 'payments']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('MemberService@renewPlan Error: ' . $e->getMessage());
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
