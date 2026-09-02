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

            // Date Range Filtering Parameters (start_date & end_date)
            $customStart = $request->query('start_date');
            $customEnd = $request->query('end_date');

            $startDate = null;
            $endDate = null;
            $isDateFiltered = false;
            $periodLabel = 'Total';

            if ($customStart && $customEnd) {
                try {
                    $startDate = \Carbon\Carbon::parse($customStart)->startOfDay();
                    $endDate = \Carbon\Carbon::parse($customEnd)->endOfDay();
                    $isDateFiltered = true;
                    $periodLabel = $startDate->format('d M') . ' - ' . $endDate->format('d M Y');
                } catch (\Exception $de) {
                    $startDate = null;
                    $endDate = null;
                    $isDateFiltered = false;
                }
            }

            // 1. Top Stats (All Data by default, or Filtered by Date Range)
            $totalMembers = Member::where('gym_id', $gymId)->count();
            $activeMembers = Member::where('gym_id', $gymId)->where('status', 'active')->count();

            if ($isDateFiltered && $startDate && $endDate) {
                // Transactions or payments between dates
                $txSum = (float) \App\Models\PaymentTransaction::where('gym_id', $gymId)
                    ->whereBetween('payment_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->sum('amount');
                
                $paySum = (float) Payment::where('gym_id', $gymId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('paid_amount');

                $collectedFees = max($txSum, $paySum);

                $pendingFees = (float) Payment::where('gym_id', $gymId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('due_amount');

                $newMembersThisPeriod = Member::where('gym_id', $gymId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count();

                $dueThisPeriod = Payment::where('gym_id', $gymId)
                    ->where('due_amount', '>', 0)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->distinct('member_id')
                    ->count('member_id');
            } else {
                // Default: All Data (Total overall stats)
                $collectedFees = (float) Payment::where('gym_id', $gymId)->sum('paid_amount');
                $pendingFees = (float) Payment::where('gym_id', $gymId)->sum('due_amount');
                $newMembersThisPeriod = $totalMembers;
                $dueThisPeriod = Payment::where('gym_id', $gymId)->where('due_amount', '>', 0)->distinct('member_id')->count('member_id');
            }

            $membersGrowth = 12;
            $activeGrowth = 8;
            $pendingGrowth = 0;
            $collectedGrowth = 18;

            // 2. 7-Day Sparkline Trend Points for Each Card
            $sparklineDays = [];
            $membersSparkline = [];
            $activeSparkline = [];
            $pendingSparkline = [];
            $collectedSparkline = [];

            for ($i = 6; $i >= 0; $i--) {
                $dayDate = now()->subDays($i)->endOfDay();
                $sparklineDays[] = $dayDate->format('d M');

                $mCountAtDay = Member::where('gym_id', $gymId)->where('created_at', '<=', $dayDate)->count();
                $aCountAtDay = Member::where('gym_id', $gymId)->where('status', 'active')->where('created_at', '<=', $dayDate)->count();
                $pSumAtDay = (float) Payment::where('gym_id', $gymId)->where('created_at', '<=', $dayDate)->sum('due_amount');
                $cSumAtDay = (float) Payment::where('gym_id', $gymId)->whereDate('created_at', $dayDate->toDateString())->sum('paid_amount');

                $membersSparkline[] = $mCountAtDay;
                $activeSparkline[] = $aCountAtDay;
                $pendingSparkline[] = $pSumAtDay;
                $collectedSparkline[] = $cSumAtDay;
            }

            // 3. Important Alerts
            $membersForExpiry = Member::with('plan')->where('gym_id', $gymId)->whereNotNull('plan_id')->get();
            $expiringSoon = 0;
            $expiredThisMonth = 0;
            
            $now = now();
            $sevenDaysFromNow = now()->addDays(7);
            $startOfMonth = now()->startOfMonth();
            
            foreach($membersForExpiry as $member) {
                if ($member->plan && $member->plan->duration_months) {
                    $endDateExpiry = \Carbon\Carbon::parse($member->joining_date)->addMonths($member->plan->duration_months);
                    if ($endDateExpiry->between($now, $sevenDaysFromNow)) {
                        $expiringSoon++;
                    }
                    if ($endDateExpiry->isPast() && $endDateExpiry->between($startOfMonth, $now)) {
                        $expiredThisMonth++;
                    }
                }
            }

            $dueThisMonth = $dueThisPeriod;
            $newMembersThisMonth = $newMembersThisPeriod;

            // 4. Attendance Today (100% Dynamic)
            $presentToday = Attendance::where('gym_id', $gymId)->whereDate('date', today())->where('status', 'P')->count();
            $absentMarked = Attendance::where('gym_id', $gymId)->whereDate('date', today())->where('status', 'A')->count();
            $totalAttendanceMarked = Attendance::where('gym_id', $gymId)->whereDate('date', today())->count();

            if ($totalAttendanceMarked > 0) {
                $totalAttendanceToday = $totalAttendanceMarked;
                $absentToday = $absentMarked;
            } else {
                $totalAttendanceToday = $activeMembers > 0 ? $activeMembers : 1;
                $absentToday = max(0, $totalAttendanceToday - $presentToday);
            }
            $attendancePercentage = $totalAttendanceToday > 0 ? round(($presentToday / $totalAttendanceToday) * 100) : 0;

            // 5. Monthly Overview (Last 6 Months)
            $months = [];
            $collectionData = [];
            $pendingData = [];
            $expenseData = [];

            for ($i = 5; $i >= 0; $i--) {
                $monthDate = now()->subMonths($i);
                $monthKey = $monthDate->format('M');
                $m = $monthDate->month;
                $y = $monthDate->year;
                $months[] = $monthKey;

                $mCollected = (float) Payment::where('gym_id', $gymId)->whereMonth('created_at', $m)->whereYear('created_at', $y)->sum('paid_amount');
                $mPending = (float) Payment::where('gym_id', $gymId)->whereMonth('created_at', $m)->whereYear('created_at', $y)->sum('due_amount');
                $mExpense = (float) \App\Models\Expense::where('gym_id', $gymId)->whereMonth('expense_date', $m)->whereYear('expense_date', $y)->sum('amount');

                $collectionData[] = $mCollected;
                $pendingData[] = $mPending;
                $expenseData[] = $mExpense;
            }

            // 6. Top Plans (100% Dynamic)
            $plans = \App\Models\Plan::where('gym_id', $gymId)->get();
            $topPlansList = [];
            foreach ($plans as $plan) {
                $mCount = Member::where('gym_id', $gymId)->where('plan_id', $plan->id)->count();
                $topPlansList[] = [
                    'id' => $plan->id,
                    'name' => $plan->plan_group_name,
                    'members' => $mCount,
                    'percentage' => $totalMembers > 0 ? round(($mCount / $totalMembers) * 100) : 0
                ];
            }
            usort($topPlansList, function($a, $b) { return $b['members'] <=> $a['members']; });
            $topPlansList = array_slice($topPlansList, 0, 3);

            // 7. Recent Activities (100% Dynamic)
            $recentActivities = [];
            
            // Recent members
            $recentJoined = Member::with('user')->where('gym_id', $gymId)->orderBy('created_at', 'desc')->take(3)->get();
            foreach ($recentJoined as $rm) {
                $recentActivities[] = [
                    'type' => 'member',
                    'timestamp' => $rm->created_at ? $rm->created_at->timestamp : 0,
                    'icon' => 'fa-user-plus',
                    'bg_color' => 'bg-emerald-50 text-emerald-500',
                    'title' => 'New member <span class="font-bold">' . ($rm->user ? e($rm->user->name) : 'Member') . '</span> joined',
                    'time' => $rm->created_at ? $rm->created_at->diffForHumans() : 'Recently',
                    'badge' => null,
                    'badge_color' => ''
                ];
            }

            // Recent payments
            $recentPayments = Payment::with('member.user')->where('gym_id', $gymId)->orderBy('created_at', 'desc')->take(3)->get();
            foreach ($recentPayments as $rp) {
                $mName = ($rp->member && $rp->member->user) ? $rp->member->user->name : 'Member';
                $recentActivities[] = [
                    'type' => 'payment',
                    'timestamp' => $rp->created_at ? $rp->created_at->timestamp : 0,
                    'icon' => 'fa-indian-rupee-sign',
                    'bg_color' => 'bg-amber-50 text-amber-500',
                    'title' => 'Payment received from <span class="font-bold">' . e($mName) . '</span>',
                    'time' => $rp->created_at ? $rp->created_at->diffForHumans() : 'Recently',
                    'badge' => '₹' . number_format($rp->paid_amount),
                    'badge_color' => 'text-emerald-600 font-bold'
                ];
            }

            // Recent expenses
            $recentExpenses = \App\Models\Expense::where('gym_id', $gymId)->orderBy('created_at', 'desc')->take(3)->get();
            foreach ($recentExpenses as $re) {
                $recentActivities[] = [
                    'type' => 'expense',
                    'timestamp' => $re->created_at ? $re->created_at->timestamp : 0,
                    'icon' => 'fa-receipt',
                    'bg_color' => 'bg-purple-50 text-purple-500',
                    'title' => 'Expense added <span class="font-bold">' . e($re->title) . '</span>',
                    'time' => $re->created_at ? $re->created_at->diffForHumans() : 'Recently',
                    'badge' => '₹' . number_format($re->amount),
                    'badge_color' => 'text-rose-500 font-bold'
                ];
            }

            // Expiring or Due Members for quick action reminder table & hover tooltips
            $dueAndExpiring = [];
            $pendingPayments = Payment::with(['member.user', 'member.plan'])
                ->where('gym_id', $gymId)
                ->where('due_amount', '>', 0)
                ->latest()
                ->take(15)
                ->get();

            foreach ($pendingPayments as $pp) {
                $dueAndExpiring[] = [
                    'payment_id' => $pp->id,
                    'member_id' => $pp->member_id,
                    'member_name' => $pp->member && $pp->member->user ? $pp->member->user->name : 'Member',
                    'mobile' => $pp->member && $pp->member->user ? $pp->member->user->mobile : '',
                    'plan_name' => $pp->member && $pp->member->plan ? $pp->member->plan->plan_group_name : 'Membership',
                    'due_amount' => (float)$pp->due_amount,
                    'total_amount' => (float)$pp->total_amount,
                    'paid_amount' => (float)$pp->paid_amount,
                    'date' => $pp->created_at ? $pp->created_at->format('d M Y') : ''
                ];
            }

            return $this->successResponse('Stats retrieved', [
                'period' => $isDateFiltered ? 'custom' : 'all_time',
                'is_date_filtered' => $isDateFiltered,
                'period_label' => $periodLabel,
                'top_stats' => [
                    'total_members' => $totalMembers,
                    'active_members' => $activeMembers,
                    'pending_fees' => $pendingFees,
                    'collected_fees' => $collectedFees,
                    'members_growth' => $membersGrowth >= 0 ? "+$membersGrowth" : "$membersGrowth",
                    'active_growth' => $activeGrowth >= 0 ? "+$activeGrowth" : "$activeGrowth",
                    'pending_growth' => $pendingGrowth >= 0 ? "+$pendingGrowth" : "$pendingGrowth",
                    'collected_growth' => $collectedGrowth >= 0 ? "+$collectedGrowth" : "$collectedGrowth",
                    'sparklines' => [
                        'members' => $membersSparkline,
                        'active' => $activeSparkline,
                        'pending' => $pendingSparkline,
                        'collected' => $collectedSparkline
                    ]
                ],
                'alerts' => [
                    'expiring_soon' => $expiringSoon,
                    'expired_month' => $expiredThisMonth,
                    'due_month' => $dueThisMonth,
                    'new_members' => $newMembersThisMonth
                ],
                'attendance_today' => [
                    'present' => $presentToday,
                    'absent' => $absentToday,
                    'total' => $totalAttendanceToday,
                    'percentage' => $attendancePercentage
                ],
                'monthly_overview' => [
                    'labels' => $months,
                    'collection' => $collectionData,
                    'pending' => $pendingData,
                    'expense' => $expenseData
                ],
                'top_plans' => $topPlansList,
                'recent_activities' => $recentActivities,
                'due_and_expiring' => $dueAndExpiring
            ]);
        } catch (Exception $e) {
            Log::error('DashboardController@getOwnerStats Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve stats.', [], 500);
        }
    }

    /**
     * Dynamic Trainer Dashboard Stats
     */
    public function getTrainerStats(Request $request)
    {
        try {
            $user = $request->user();
            if (!in_array($user->role, ['trainer', 'owner', 'staff'])) {
                return $this->errorResponse('Unauthorized.', [], 403);
            }

            $gymId = $user->gym_id;
            $trainerId = $user->role === 'trainer' ? $user->id : null;

            // Assigned members for this trainer (or all gym members if trainer has none assigned yet)
            $membersQuery = Member::with(['user', 'plan', 'batch'])
                ->where('gym_id', $gymId);
            
            if ($trainerId) {
                $assignedCount = Member::where('gym_id', $gymId)->where('trainer_id', $trainerId)->count();
                if ($assignedCount > 0) {
                    $membersQuery->where('trainer_id', $trainerId);
                }
            }

            $members = $membersQuery->latest()->get();
            $totalMembers = $members->count();
            $activeMembers = $members->where('status', 'active')->count();

            // Today's attendance
            $memberIds = $members->pluck('id')->toArray();
            $todayAttendance = Attendance::where('gym_id', $gymId)
                ->whereIn('member_id', $memberIds)
                ->whereDate('date', today())
                ->get()
                ->keyBy('member_id');

            $presentToday = $todayAttendance->where('status', 'P')->count();
            $pendingToday = max(0, $totalMembers - $presentToday);
            $attendanceRate = $totalMembers > 0 ? round(($presentToday / $totalMembers) * 100) : 0;

            // Dynamic Today's Schedule
            $todaySchedule = [];
            foreach ($members as $m) {
                $att = $todayAttendance->get($m->id);
                $isCompleted = $att && $att->status === 'P';

                $batchTiming = 'Flexible Timing';
                if ($m->batch) {
                    if (!empty($m->batch->batch_time) && !empty($m->batch->batch_name)) {
                        $batchTiming = $m->batch->batch_name . ' (' . $m->batch->batch_time . ')';
                    } elseif (!empty($m->batch->batch_time)) {
                        $batchTiming = $m->batch->batch_time;
                    } elseif (!empty($m->batch->batch_name)) {
                        $batchTiming = $m->batch->batch_name;
                    }
                }

                $todaySchedule[] = [
                    'id' => $m->id,
                    'name' => $m->user ? $m->user->name : 'Member',
                    'workout' => $m->plan ? $m->plan->plan_group_name : 'General Fitness',
                    'timing' => $batchTiming,
                    'status' => $isCompleted ? 'completed' : 'upcoming',
                    'photo' => $m->user ? $m->user->photo : null,
                    'mobile' => $m->user ? $m->user->mobile : ''
                ];
            }

            // Dynamic Recent Members
            $recentMembers = [];
            foreach ($members->take(10) as $m) {
                $recentMembers[] = [
                    'id' => $m->id,
                    'created_at' => $m->created_at ? $m->created_at->toISOString() : null,
                    'user' => [
                        'name' => $m->user ? $m->user->name : 'Member',
                        'photo' => $m->user ? $m->user->photo : null,
                        'mobile' => $m->user ? $m->user->mobile : ''
                    ],
                    'plan' => $m->plan ? $m->plan->plan_group_name : 'Membership',
                    'batch' => ($m->batch && !empty($m->batch->batch_name)) ? $m->batch->batch_name : 'General Batch'
                ];
            }

            return $this->successResponse('Trainer stats retrieved successfully', [
                'active_members' => $activeMembers,
                'total_members' => $totalMembers,
                'present_today' => $presentToday,
                'pending_today' => $pendingToday,
                'attendance_rate' => $attendanceRate,
                'today_schedule' => $todaySchedule,
                'recent_members' => $recentMembers
            ]);
        } catch (\Exception $e) {
            Log::error('DashboardController@getTrainerStats Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve trainer stats.', [], 500);
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
