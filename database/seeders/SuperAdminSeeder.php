<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SaasPlan;
use App\Models\Gym;
use App\Models\GymSubscription;
use App\Models\PlatformExpense;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create or Update Super Admin User with User's requested credentials
        $superAdmin = User::updateOrCreate(
            ['email' => 'jay@gmail.com'],
            [
                'name' => 'Jay',
                'role' => 'superadmin',
                'mobile' => '9999999999',
                'password' => Hash::make('Jay@12345'),
                'status' => 'active',
                'gym_id' => null,
            ]
        );

        // 2. Create the 2 Official Plans requested by User (Monthly ₹599, Yearly ₹6000)
        $plans = [
            [
                'name' => 'Monthly Plan',
                'code' => 'gymora_monthly',
                'price' => 599.00,
                'billing_cycle' => 'monthly',
                'max_members' => 1000,
                'description' => 'Complete Gym Management access billed monthly.',
                'features' => json_encode(['All Features Included', 'Member Management', 'Payments & Dues', 'Attendance & QR', 'Diet & Workout Planner']),
                'status' => 'active',
            ],
            [
                'name' => 'Yearly Plan',
                'code' => 'gymora_yearly',
                'price' => 6000.00,
                'billing_cycle' => 'yearly',
                'max_members' => 5000,
                'description' => 'Annual Gym Management access (Best Value: ₹500/mo).',
                'features' => json_encode(['All Features Included', '1 Year Full Access', 'Priority Support', 'Cloud Backup', 'Annual Savings']),
                'status' => 'active',
            ],
        ];

        foreach ($plans as $p) {
            SaasPlan::updateOrCreate(['code' => $p['code']], $p);
        }

        // 3. Platform Expenses (AWS Server Hosting, SMS, Domain)
        $expenses = [
            [
                'title' => 'Cloud Server & Database Hosting',
                'category' => 'Hosting & Cloud',
                'amount' => 1800.00,
                'expense_date' => Carbon::now()->startOfMonth()->toDateString(),
                'vendor_name' => 'AWS / Cloud Hosting',
                'payment_method' => 'UPI',
                'notes' => 'Monthly server hosting cost.',
            ],
            [
                'title' => 'Bulk WhatsApp & SMS Gateway Alert Credits',
                'category' => 'SMS & WhatsApp API',
                'amount' => 600.00,
                'expense_date' => Carbon::now()->subDays(3)->toDateString(),
                'vendor_name' => 'SMS Gateway Provider',
                'payment_method' => 'UPI',
                'notes' => 'Monthly WhatsApp & SMS API credit recharge.',
            ],
        ];

        foreach ($expenses as $exp) {
            PlatformExpense::firstOrCreate(['title' => $exp['title'], 'expense_date' => $exp['expense_date']], $exp);
        }

        // 4. Auto-assign existing gyms if any
        $gyms = Gym::all();
        foreach ($gyms as $gym) {
            if ($gym->subscriptions()->count() === 0) {
                GymSubscription::create([
                    'gym_id' => $gym->id,
                    'plan_name' => 'Monthly Plan',
                    'amount_paid' => 599.00,
                    'billing_cycle' => 'monthly',
                    'start_date' => Carbon::now()->subDays(5),
                    'end_date' => Carbon::now()->addDays(25),
                    'payment_method' => 'UPI',
                    'payment_status' => 'paid',
                    'status' => 'active',
                    'notes' => 'Initial subscription setup',
                ]);

                $gym->update([
                    'status' => 'active',
                    'subscription_end_date' => Carbon::now()->addDays(25),
                ]);
            }
        }
    }
}
