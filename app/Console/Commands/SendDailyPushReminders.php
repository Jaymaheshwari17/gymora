<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SendDailyPushReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:push-reminders {type=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends automated Expo Push Notifications for Birthdays, Plan Expiries, and Overdue Fees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $this->info('Starting ' . ucfirst($type) . ' Push Reminders job...');
        $today = Carbon::today();
        
        $notificationsToSend = [];

        // ================= MORNING NOTIFICATIONS =================
        if ($type === 'morning' || $type === 'all') {
            
            // 1. Birthdays (DOB is on the users table)
        $birthdayMembers = Member::whereHas('user', function ($query) use ($today) {
                $query->whereMonth('dob', $today->month)
                      ->whereDay('dob', $today->day);
            })
            ->where('status', 'active')
            ->with(['user', 'gym'])
            ->get();

        foreach ($birthdayMembers as $member) {
            if ($member->user && $member->user->push_token) {
                $gymName = $member->gym ? $member->gym->name : 'us';
                $notificationsToSend[] = [
                    'to' => $member->user->push_token,
                    'title' => '🎉 Happy Birthday ' . $member->user->name . '!',
                    'body' => 'Wishing you a fantastic day and a great workout from the ' . $gymName . ' team!',
                    'sound' => 'default',
                ];
            }
        }

        // 2. Expiry in 3 Days
        $allMembers = Member::where('status', 'active')->with(['plan', 'user', 'gym'])->get();
        foreach ($allMembers as $member) {
            if ($member->plan && $member->joining_date) {
                $endDate = Carbon::parse($member->joining_date)->addMonths($member->plan->duration_months);
                if ($endDate->copy()->subDays(3)->isSameDay($today)) {
                    if ($member->user && $member->user->push_token) {
                        $notificationsToSend[] = [
                            'to' => $member->user->push_token,
                            'title' => '⚠️ Plan Expiring Soon',
                            'body' => 'Your gym membership expires in 3 days. Please renew to continue without interruption.',
                            'sound' => 'default',
                        ];
                    }
                }
            }
        }

        // 3. Fee Overdue (More than 7 days passed since joining or last payment, and due_amount > 0)
        // For simplicity, checking latest payment of active members
        foreach ($allMembers as $member) {
            $latestPayment = Payment::where('member_id', $member->id)->orderBy('id', 'desc')->first();
            $dueAmount = $latestPayment ? $latestPayment->due_amount : ($member->plan_amount - $member->discount);
            
            if ($dueAmount > 0) {
                // If joining_date was more than 7 days ago
                $joinDate = Carbon::parse($member->joining_date);
                if ($today->diffInDays($joinDate) >= 7) {
                    if ($member->user && $member->user->push_token) {
                        $notificationsToSend[] = [
                            'to' => $member->user->push_token,
                            'title' => '💰 Fee Reminder',
                            'body' => 'Gentle reminder: You have a pending fee of ₹' . $dueAmount . '. Please clear it soon.',
                            'sound' => 'default',
                        ];
                    }
                }
            }
        }

        // ================= EVENING NOTIFICATIONS =================
        if ($type === 'evening' || $type === 'all') {
            
            // 4. Owner Notifications (Business Summary)
        $owners = \App\Models\User::where('role', 'owner')
            ->whereNotNull('push_token')
            ->where('status', 'active')
            ->with('gym')
            ->get();

        foreach ($owners as $owner) {
            $gymId = $owner->gym_id;
            $gymName = $owner->gym ? $owner->gym->name : 'Your Gym';

            // Daily Revenue
            $todayRevenue = Payment::where('gym_id', $gymId)
                ->whereDate('payment_date', $today)
                ->sum('paid_amount');

            if ($todayRevenue > 0) {
                $notificationsToSend[] = [
                    'to' => $owner->push_token,
                    'title' => '💰 Daily Revenue: ' . $gymName,
                    'body' => 'You collected ₹' . number_format($todayRevenue) . ' today. Keep it up!',
                    'sound' => 'default',
                ];
            }

            // Retention Alert (Expiring in 7 days)
            $expiringCount = 0;
            $gymMembers = Member::where('gym_id', $gymId)->where('status', 'active')->with('plan')->get();
            foreach ($gymMembers as $m) {
                if ($m->plan && $m->joining_date) {
                    $endDate = Carbon::parse($m->joining_date)->addMonths($m->plan->duration_months);
                    if ($endDate->diffInDays($today) <= 7 && $endDate->isFuture()) {
                        $expiringCount++;
                    }
                }
            }
                if ($expiringCount > 0) {
                    $notificationsToSend[] = [
                        'to' => $owner->push_token,
                        'title' => '⚠️ Renewals Due: ' . $gymName,
                        'body' => $expiringCount . ' members have their plans expiring in the next 7 days. Time to follow up!',
                        'sound' => 'default',
                    ];
                }
            }
        }

        // Send via Expo Push API
        if (count($notificationsToSend) > 0) {
            $this->info('Sending ' . count($notificationsToSend) . ' push notifications via Expo...');
            
            // Expo accepts array of messages
            $response = Http::post('https://exp.host/--/api/v2/push/send', $notificationsToSend);
            
            if ($response->successful()) {
                $this->info('Successfully sent push notifications!');
                Log::info('Expo Push Success: ' . $response->body());
            } else {
                $this->error('Failed to send push notifications.');
                Log::error('Expo Push Failed: ' . $response->body());
            }
        } else {
            $this->info('No push notifications to send today.');
        }
    }
}
