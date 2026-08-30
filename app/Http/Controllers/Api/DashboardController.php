<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Models\Attendance;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class DashboardController extends Controller
{
    use ApiResponse;

    public function getOwnerStats(Request $request)
    {
        try {
            $allowedRoles = ['owner', 'staff', 'trainer'];
            if (!in_array($request->user()->role, $allowedRoles)) {
                return $this->errorResponse('Unauthorized.', [], 403);
            }
            
            $gymId = $request->user()->gym_id;

            // 1. Top Stats
            $totalMembers = Member::where('gym_id', $gymId)->count();
            $activeMembers = Member::where('gym_id', $gymId)->where('status', 'active')->count();
            
            $pendingFees = (float) Payment::where('gym_id', $gymId)->sum('due_amount');
            $collectedFees = (float) Payment::where('gym_id', $gymId)->sum('paid_amount');

            // 2. Revenue Chart (Last 7 Days)
            $sevenDaysAgo = now()->subDays(6)->startOfDay();
            $revenueData = Payment::where('gym_id', $gymId)
                ->where('created_at', '>=', $sevenDaysAgo)
                ->selectRaw('DATE(created_at) as date, SUM(paid_amount) as total')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();

            $chartLabels = [];
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $chartLabels[] = now()->subDays($i)->format('d M');
                $match = $revenueData->firstWhere('date', $date);
                $chartData[] = $match ? (float) $match->total : 0;
            }

            // 3. Revenue Pie Chart (Mock others since only membership is tracked)
            $revenuePie = [
                'membership' => $collectedFees,
                'pt' => 0,
                'diet' => 0,
                'other' => 0
            ];

            // 4. Attendance
            $presentToday = Attendance::where('gym_id', $gymId)->whereDate('date', today())->where('status', 'P')->count();
            $absentToday = max(0, $activeMembers - $presentToday);
            $attendanceRate = $activeMembers > 0 ? round(($presentToday / $activeMembers) * 100) : 0;

            // 5. Bottom Lists
            $recentMembers = Member::with(['user', 'plan', 'trainer'])
                ->where('gym_id', $gymId)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            // Today's Member Schedules / Workouts
            $todaySchedule = Member::with(['user', 'batch'])
                ->where('gym_id', $gymId)
                ->where('status', 'active')
                ->take(6)
                ->get()
                ->map(function ($m) use ($gymId) {
                    $att = Attendance::where('gym_id', $gymId)
                        ->where('member_id', $m->id)
                        ->whereDate('date', today())
                        ->first();
                    $isCompleted = $att && $att->status === 'P';

                    $timeStr = '7:00 AM – 8:00 AM';
                    if ($m->batch && ($m->batch->start_time || $m->batch->end_time)) {
                        $timeStr = ($m->batch->start_time ? date('g:i A', strtotime($m->batch->start_time)) : '') . ' – ' . 
                                   ($m->batch->end_time ? date('g:i A', strtotime($m->batch->end_time)) : '');
                    }

                    return [
                        'id' => $m->id,
                        'name' => $m->user ? $m->user->name : 'Member',
                        'photo' => $m->user ? $m->user->photo : null,
                        'workout' => $m->plan ? $m->plan->plan_group_name : 'General Workout',
                        'timing' => $timeStr,
                        'status' => $isCompleted ? 'completed' : 'upcoming',
                    ];
                });

            $upcomingBirthdays = User::where('gym_id', $gymId)
                ->where('role', 'member')
                ->whereNotNull('dob')
                ->whereRaw("DATE_FORMAT(dob, '%m-%d') >= ?", [now()->format('m-d')])
                ->orderByRaw("DATE_FORMAT(dob, '%m-%d') ASC")
                ->take(5)
                ->get();
                
            $todaysBirthdays = User::where('gym_id', $gymId)
                ->where('role', 'member')
                ->whereNotNull('dob')
                ->whereMonth('dob', now()->month)
                ->whereDay('dob', now()->day)
                ->get();

            // 6. Bottom Chips
            $newMembersToday = Member::where('gym_id', $gymId)->whereDate('created_at', today())->count();
            $newTrainersMonth = User::where('gym_id', $gymId)->where('role', 'trainer')->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
            $dietPlansAssigned = \App\Models\DietPlan::where('gym_id', $gymId)->count();
            $workoutPlansAssigned = \App\Models\WorkoutPlan::where('gym_id', $gymId)->count();

            // 7. Important Alerts
            $membersForExpiry = Member::with('plan')->where('gym_id', $gymId)->whereNotNull('plan_id')->get();
            $expiringSoon = 0;
            $expiredThisMonth = 0;
            
            $now = now();
            $sevenDaysFromNow = now()->addDays(7);
            $startOfMonth = now()->startOfMonth();
            
            foreach($membersForExpiry as $member) {
                if ($member->plan && $member->plan->duration_months) {
                    $endDate = \Carbon\Carbon::parse($member->joining_date)->addMonths($member->plan->duration_months);
                    if ($endDate->between($now, $sevenDaysFromNow)) {
                        $expiringSoon++;
                    }
                    if ($endDate->isPast() && $endDate->between($startOfMonth, $now)) {
                        $expiredThisMonth++;
                    }
                }
            }

            $dueThisMonth = Payment::where('gym_id', $gymId)
                ->where('due_amount', '>', 0)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->distinct('member_id')
                ->count('member_id');

            return $this->successResponse('Stats retrieved', [
                'top_stats' => [
                    'total_members' => $totalMembers,
                    'active_members' => $activeMembers,
                    'pending_fees' => $pendingFees,
                    'collected_fees' => $collectedFees,
                ],
                'charts' => [
                    'revenue_line' => [
                        'labels' => $chartLabels,
                        'data' => $chartData
                    ],
                    'revenue_pie' => $revenuePie,
                    'attendance' => [
                        'present' => $presentToday,
                        'absent' => $absentToday
                    ]
                ],
                'lists' => [
                    'recent_members' => $recentMembers,
                    'today_schedule' => $todaySchedule,
                    'upcoming_birthdays' => $upcomingBirthdays,
                    'todays_birthdays' => $todaysBirthdays
                ],
                'alerts' => [
                    'expiring_soon' => $expiringSoon,
                    'expired_month' => $expiredThisMonth,
                    'due_month' => $dueThisMonth
                ],
                'chips' => [
                    'new_members' => $newMembersToday,
                    'new_trainers' => $newTrainersMonth,
                    'diet_plans' => $dietPlansAssigned,
                    'workout_plans' => $workoutPlansAssigned,
                    'attendance_rate' => $attendanceRate
                ]
            ]);
        } catch (Exception $e) {
            Log::error('DashboardController@getOwnerStats Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve stats.', [], 500);
        }
    }

    public function getNotifications(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;
            $notifications = [];

            // 1. Expiring Soon (Next 7 days)
            $membersForExpiry = Member::with('plan')->where('gym_id', $gymId)->whereNotNull('plan_id')->where('status', 'active')->get();
            $expiringSoon = 0;
            $now = now();
            $sevenDaysFromNow = now()->addDays(7);
            
            foreach($membersForExpiry as $member) {
                if ($member->plan && $member->plan->duration_months) {
                    $endDate = \Carbon\Carbon::parse($member->joining_date)->addMonths($member->plan->duration_months);
                    if ($endDate->between($now, $sevenDaysFromNow)) {
                        $expiringSoon++;
                    }
                }
            }
            
            if ($expiringSoon > 0) {
                $notifications[] = [
                    'id' => 'expiring',
                    'icon' => 'fa-clock',
                    'color' => 'text-yellow-500',
                    'bg' => 'bg-yellow-50',
                    'title' => 'Expiring Soon',
                    'message' => "$expiringSoon member(s) have plans expiring in the next 7 days.",
                    'time' => 'Action Required'
                ];
            }

            // 2. Pending Fees
            $pendingFees = Payment::where('gym_id', $gymId)
                ->where('due_amount', '>', 0)
                ->count();
            if ($pendingFees > 0) {
                $notifications[] = [
                    'id' => 'fees',
                    'icon' => 'fa-money-bill-wave',
                    'color' => 'text-red-500',
                    'bg' => 'bg-red-50',
                    'title' => 'Pending Payments',
                    'message' => "There are $pendingFees pending payments.",
                    'time' => 'Action Required'
                ];
            }

            // 3. Birthdays Today
            $birthdays = User::where('gym_id', $gymId)
                ->where('role', 'member')
                ->whereNotNull('dob')
                ->whereMonth('dob', now()->month)
                ->whereDay('dob', now()->day)
                ->count();
            if ($birthdays > 0) {
                $notifications[] = [
                    'id' => 'birthday',
                    'icon' => 'fa-cake-candles',
                    'color' => 'text-pink-500',
                    'bg' => 'bg-pink-50',
                    'title' => 'Birthdays Today',
                    'message' => "It is $birthdays member(s) birthday today!",
                    'time' => 'Today'
                ];
            }

            // 4. New Registrations Today
            $newMembers = Member::where('gym_id', $gymId)
                ->whereDate('created_at', today())
                ->count();
            if ($newMembers > 0) {
                $notifications[] = [
                    'id' => 'new_members',
                    'icon' => 'fa-user-plus',
                    'color' => 'text-green-500',
                    'bg' => 'bg-green-50',
                    'title' => 'New Members',
                    'message' => "$newMembers new member(s) registered today.",
                    'time' => 'Today'
                ];
            }

            // Fallback notification if empty
            if (empty($notifications)) {
                $notifications[] = [
                    'id' => 'welcome',
                    'icon' => 'fa-bell',
                    'color' => 'text-blue-500',
                    'bg' => 'bg-blue-50',
                    'title' => 'All Caught Up!',
                    'message' => "You have no new alerts right now.",
                    'time' => 'Just now'
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'unread_count' => count(array_filter($notifications, fn($n) => $n['id'] !== 'welcome'))
            ]);

        } catch (Exception $e) {
            Log::error('DashboardController@getNotifications: ' . $e->getMessage());
            return $this->errorResponse('Failed to load notifications.', [], 500);
        }
    }
    public function getMemberStats(Request $request)
    {
        try {
            $user = $request->user();
            if ($user->role !== 'member') {
                return $this->errorResponse('Unauthorized.', [], 403);
            }

            $member = Member::with(['trainer', 'plan', 'batch'])
                ->where('user_id', $user->id)
                ->first();

            $user->load('gym');

            if (!$member) {
                return $this->errorResponse('Member profile not found.', [], 404);
            }

            // Financials
            $latestPayment = Payment::where('member_id', $member->id)->orderBy('id', 'desc')->first();
            
            $financials = [
                'total_amount' => $latestPayment ? $latestPayment->total_amount : ($member->plan_amount - $member->discount),
                'paid_amount' => $latestPayment ? $latestPayment->paid_amount : 0,
                'due_amount' => $latestPayment ? $latestPayment->due_amount : ($member->plan_amount - $member->discount),
                'last_payment_date' => $latestPayment ? $latestPayment->payment_date : null,
            ];

            // Attendance (Current Month)
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $daysInMonth = now()->daysInMonth;
            
            $presentDays = Attendance::where('member_id', $member->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'P')
                ->count();

            $joinDate = \Carbon\Carbon::parse($member->joining_date)->startOfDay();
            $today = now()->startOfDay();
            $totalDaysSoFar = $today->day;
            
            if ($joinDate->isCurrentMonth()) {
                $totalDaysSoFar = max(1, $today->diffInDays($joinDate) + 1); // +1 because joining day counts
            } elseif ($joinDate->isFuture()) {
                $totalDaysSoFar = 0;
            }

            // Fallback safety to ensure total_days is at least present_days
            $totalDaysSoFar = max($totalDaysSoFar, $presentDays);

            // Get full history for the current month
            $historyRecords = Attendance::where('member_id', $member->id)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->orderBy('date', 'desc')
                ->get(['date', 'status', 'created_at']);

            $is_present_today = $historyRecords->where('date', now()->format('Y-m-d'))->where('status', 'P')->count() > 0;
                
            $attendance = [
                'present_days' => $presentDays,
                'total_days_so_far' => $totalDaysSoFar,
                'streak' => 0, // To be implemented with complex query if needed, keeping 0 for now
                'is_present_today' => $is_present_today,
                'history' => $historyRecords
            ];

            // Active Plans
            $activeDiet = \App\Models\MemberDietPlan::with('dietPlan')
                ->where('member_id', $member->id)
                ->where('status', 'active')
                ->first();

            $activeWorkout = \App\Models\MemberWorkoutPlan::with('workoutPlan')
                ->where('member_id', $member->id)
                ->where('status', 'active')
                ->first();

            // Notifications for this member (distinct by title, latest first)
            $memberNotifications = \App\Models\FcmNotification::where('user_id', $user->id)
                ->latest()
                ->get()
                ->unique('title')
                ->take(5)
                ->values()
                ->map(function ($n) {
                    return [
                        'id' => $n->id,
                        'title' => $n->title,
                        'message' => $n->body,
                        'time' => $n->created_at->diffForHumans(),
                        'data' => $n->data,
                    ];
                });

            $isBirthdayToday = $user->dob && ($user->dob->month === now()->month && $user->dob->day === now()->day);

            return $this->successResponse('Member stats retrieved', [
                'profile' => [
                    'member_id' => $member->id,
                    'name' => $user->name,
                    'photo' => $user->photo,
                    'joining_date' => $member->joining_date,
                    'status' => $member->status,
                ],
                'is_birthday_today' => $isBirthdayToday,
                'notifications' => $memberNotifications,
                'gym_name' => $user->gym ? $user->gym->name : 'My Gym',
                'trainer' => $member->trainer ? [
                    'id' => $member->trainer->id,
                    'name' => $member->trainer->name ?? 'Unknown',
                    'photo' => $member->trainer->photo ?? null,
                    'specialization' => $member->trainer->specialization ?? 'General Training',
                    'experience_years' => $member->trainer->experience_years ?? 0,
                    'mobile' => $member->trainer->mobile,
                    'email' => $member->trainer->email,
                ] : null,
                'membership_plan' => $member->plan ? [
                    'name' => $member->plan->plan_group_name,
                    'duration' => $member->plan->duration_months,
                ] : null,
                'financials' => $financials,
                'attendance' => $attendance,
                'active_diet' => $activeDiet ? [
                    'id' => $activeDiet->dietPlan->id,
                    'title' => $activeDiet->dietPlan->title,
                ] : null,
                'active_workout' => $activeWorkout ? [
                    'id' => $activeWorkout->workoutPlan->id,
                    'title' => $activeWorkout->workoutPlan->title,
                ] : null,
            ]);

        } catch (Exception $e) {
            Log::error('DashboardController@getMemberStats: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch member stats.', [], 500);
        }
    }
}
