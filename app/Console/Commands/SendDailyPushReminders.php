<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\Payment;
use App\Models\User;
use App\Models\FcmNotification;
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
    protected $signature = 'send:push-reminders {type=all} {--token= : Send a test notification to a specific Expo push token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends automated push notifications for Birthdays, Plan Expiries, and Pending Fee reminders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $testToken = $this->option('token');
        $this->info("==================================================");
        $this->info("🚀 Starting {$type} Push Reminders job at " . now()->toDateTimeString());
        $this->info("==================================================");
        $today = Carbon::today();
        
        $notificationsToSend = [];

        // Direct Test Mode (if --token is provided)
        if ($testToken) {
            $this->info("🔔 Sending direct test push notification to token: {$testToken}");
            $testNotification = [
                'to' => $testToken,
                'title' => '🏋️ Gymora Live Push Test',
                'body' => 'Congratulations! Push notification system is working perfectly on your device! 🎉',
                'sound' => 'default',
                'data' => ['type' => 'test'],
            ];
            $this->sendPushNotifications([$testNotification]);
            return;
        }

        // ================= MORNING NOTIFICATIONS (8:00 AM) =================
        if ($type === 'morning' || $type === 'all') {
            
            // 1. Birthday Notifications (Users whose DOB is today)
            $birthdayMembers = Member::whereHas('user', function ($query) use ($today) {
                $query->whereMonth('dob', $today->month)
                      ->whereDay('dob', $today->day);
            })
            ->where('status', 'active')
            ->with(['user', 'gym'])
            ->get();

            $this->info("🎂 Birthday check: Found " . $birthdayMembers->count() . " active members celebrating birthday today.");

            foreach ($birthdayMembers as $member) {
                if ($member->user) {
                    $gymName = $member->gym ? $member->gym->name : 'Your Gym';
                    $title = '🎉 Happy Birthday ' . $member->user->name . '!';
                    $body = "Wishing you a fantastic day filled with health and strength from the {$gymName} team! 💪";

                    // Save in-app notification
                    $this->saveInAppNotification($member->user->id, $title, $body, ['type' => 'birthday']);
                    $this->line("  ✓ Generated Birthday Wish for: {$member->user->name}");

                    // Prepare push notification
                    $token = $member->user->push_token ?? $member->user->fcm_device_token;
                    if ($token) {
                        $notificationsToSend[] = [
                            'to' => $token,
                            'title' => $title,
                            'body' => $body,
                            'sound' => 'default',
                            'data' => ['type' => 'birthday'],
                        ];
                    } else {
                        $this->warn("    (No push_token found in database for {$member->user->name} yet)");
                    }
                }
            }

            // 2. Plan Expiry Reminders (Expiring Today, In 3 Days, or Expired Yesterday)
            $activeMembers = Member::where('status', 'active')->with(['plan', 'user', 'gym'])->get();
            $expiryCount = 0;

            foreach ($activeMembers as $member) {
                if ($member->plan && $member->joining_date && $member->user) {
                    $gymName = $member->gym ? $member->gym->name : 'Your Gym';
                    $endDate = Carbon::parse($member->joining_date)->addMonths($member->plan->duration_months);
                    $token = $member->user->push_token ?? $member->user->fcm_device_token;

                    // A) Expiring TODAY
                    if ($endDate->isSameDay($today)) {
                        $expiryCount++;
                        $title = '🚨 Plan Expiring Today!';
                        $body = "Hi {$member->user->name}, your gym membership at {$gymName} expires today. Please renew today to keep working out smoothly!";

                        $this->saveInAppNotification($member->user->id, $title, $body, ['type' => 'plan_expiry_today']);
                        $this->line("  ✓ Plan Expiring Today: {$member->user->name}");

                        if ($token) {
                            $notificationsToSend[] = [
                                'to' => $token,
                                'title' => $title,
                                'body' => $body,
                                'sound' => 'default',
                                'data' => ['type' => 'plan_expiry_today'],
                            ];
                        }
                    }
                    // B) Expiring in 3 Days
                    elseif ($endDate->copy()->subDays(3)->isSameDay($today)) {
                        $expiryCount++;
                        $title = '⚠️ Plan Expiring in 3 Days';
                        $body = "Hi {$member->user->name}, your gym membership at {$gymName} expires in 3 days. Please renew to continue without interruption.";

                        $this->saveInAppNotification($member->user->id, $title, $body, ['type' => 'plan_expiry_soon']);
                        $this->line("  ✓ Plan Expiring in 3 Days: {$member->user->name}");

                        if ($token) {
                            $notificationsToSend[] = [
                                'to' => $token,
                                'title' => $title,
                                'body' => $body,
                                'sound' => 'default',
                                'data' => ['type' => 'plan_expiry_soon'],
                            ];
                        }
                    }
                    // C) Expired 1 Day Ago
                    elseif ($endDate->copy()->addDay()->isSameDay($today)) {
                        $expiryCount++;
                        $title = '⏳ Membership Plan Expired';
                        $body = "Hi {$member->user->name}, your membership at {$gymName} has expired. Please visit the front desk to renew your plan.";

                        $this->saveInAppNotification($member->user->id, $title, $body, ['type' => 'plan_expired']);
                        $this->line("  ✓ Plan Expired Yesterday: {$member->user->name}");

                        if ($token) {
                            $notificationsToSend[] = [
                                'to' => $token,
                                'title' => $title,
                                'body' => $body,
                                'sound' => 'default',
                                'data' => ['type' => 'plan_expired'],
                            ];
                        }
                    }
                }
            }
            $this->info("⏳ Plan Expiry check: Found {$expiryCount} members with upcoming or recent expiries.");

            // 3. Pending Fee Reminders
            $dueFeeCount = 0;
            foreach ($activeMembers as $member) {
                if ($member->user) {
                    $latestPayment = Payment::where('member_id', $member->id)->orderBy('id', 'desc')->first();
                    $dueAmount = $latestPayment ? (float)$latestPayment->due_amount : (float)max(0, $member->total_amount - ($member->discount ?? 0));

                    if ($dueAmount > 0) {
                        $dueFeeCount++;
                        $gymName = $member->gym ? $member->gym->name : 'Your Gym';
                        $title = '💰 Pending Fee Reminder';
                        $body = "Hi {$member->user->name}, gentle reminder: you have a pending fee of ₹" . number_format($dueAmount) . " at {$gymName}. Please clear it soon.";

                        $this->saveInAppNotification($member->user->id, $title, $body, ['type' => 'fee_pending', 'due_amount' => $dueAmount]);
                        $this->line("  ✓ Fee Pending (₹" . number_format($dueAmount) . ") for: {$member->user->name}");

                        $token = $member->user->push_token ?? $member->user->fcm_device_token;
                        if ($token) {
                            $notificationsToSend[] = [
                                'to' => $token,
                                'title' => $title,
                                'body' => $body,
                                'sound' => 'default',
                                'data' => ['type' => 'fee_pending', 'due_amount' => $dueAmount],
                            ];
                        }
                    }
                }
            }
            $this->info("💰 Fee Pending check: Found {$dueFeeCount} members with pending dues.");
        }

        // ================= EVENING NOTIFICATIONS (9:00 PM) =================
        if ($type === 'evening' || $type === 'all') {
            
            // 4. Owner Notifications (Daily Revenue and Retention Summary)
            $owners = User::where('role', 'owner')
                ->where('status', 'active')
                ->with('gym')
                ->get();

            $this->info("👔 Owner check: Found " . $owners->count() . " active gym owners.");

            foreach ($owners as $owner) {
                $gymId = $owner->gym_id;
                $gymName = $owner->gym ? $owner->gym->name : 'Your Gym';
                $token = $owner->push_token ?? $owner->fcm_device_token;

                // Daily Revenue Collected
                $todayRevenue = Payment::where('gym_id', $gymId)
                    ->whereDate('payment_date', $today)
                    ->sum('paid_amount');

                if ($todayRevenue > 0) {
                    $title = "💰 Daily Revenue: {$gymName}";
                    $body = "You collected ₹" . number_format($todayRevenue) . " today. Keep up the great momentum!";

                    $this->saveInAppNotification($owner->id, $title, $body, ['type' => 'owner_daily_revenue']);
                    $this->line("  ✓ Owner Revenue Summary for {$owner->name}: ₹" . number_format($todayRevenue));

                    if ($token) {
                        $notificationsToSend[] = [
                            'to' => $token,
                            'title' => $title,
                            'body' => $body,
                            'sound' => 'default',
                            'data' => ['type' => 'owner_daily_revenue'],
                        ];
                    }
                }

                // Renewals due in the next 7 days
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
                    $title = "⚠️ Renewals Due: {$gymName}";
                    $body = "{$expiringCount} members have plans expiring in the next 7 days. Time to follow up!";

                    $this->saveInAppNotification($owner->id, $title, $body, ['type' => 'owner_expiring_plans']);
                    $this->line("  ✓ Owner Renewals Alert for {$owner->name}: {$expiringCount} expiring plans");

                    if ($token) {
                        $notificationsToSend[] = [
                            'to' => $token,
                            'title' => $title,
                            'body' => $body,
                            'sound' => 'default',
                            'data' => ['type' => 'owner_expiring_plans'],
                        ];
                    }
                }
            }
        }

        // ================= SEND PUSH NOTIFICATIONS VIA EXPO API =================
        $this->sendPushNotifications($notificationsToSend);

        $this->info("==================================================");
        $this->info("✅ Push Reminders job finished successfully.");
        $this->info("==================================================");
    }

    /**
     * Dispatch array of push notification payloads via Expo Push API.
     */
    protected function sendPushNotifications(array $notifications)
    {
        if (count($notifications) > 0) {
            $this->info("📤 Sending " . count($notifications) . " push notification(s) via Expo Push API...");
            
            $chunks = array_chunk($notifications, 100);
            foreach ($chunks as $chunk) {
                try {
                    $response = Http::timeout(15)->post('https://exp.host/--/api/v2/push/send', $chunk);
                    
                    if ($response->successful()) {
                        $this->info("  ✓ Successfully dispatched push notifications to Expo!");
                        Log::info('Expo Push Batch Sent: ' . count($chunk) . ' notifications.');
                    } else {
                        $this->error("  ✗ Failed to dispatch to Expo: " . $response->body());
                        Log::error('Expo Push API Error: ' . $response->body());
                    }
                } catch (\Exception $e) {
                    $this->error("  ✗ Expo Push Exception: " . $e->getMessage());
                    Log::error('Expo Push Exception: ' . $e->getMessage());
                }
            }
        } else {
            $this->comment("ℹ️ No mobile push_tokens found in DB for today's candidates. In-app notifications were saved.");
        }
    }

    /**
     * Helper to save notification in the database for the user.
     */
    protected function saveInAppNotification(int $userId, string $title, string $body, array $data = [])
    {
        try {
            // Prevent duplicate records for same user & title on the same day
            $alreadyExists = FcmNotification::where('user_id', $userId)
                ->where('title', $title)
                ->whereDate('created_at', today())
                ->exists();

            if (!$alreadyExists) {
                FcmNotification::create([
                    'user_id' => $userId,
                    'title' => $title,
                    'body' => $body,
                    'data' => $data,
                    'is_read' => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("Could not save in-app notification for user {$userId}: " . $e->getMessage());
        }
    }
}
