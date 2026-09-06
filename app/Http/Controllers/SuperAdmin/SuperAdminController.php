<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Gym;
use App\Models\GymSubscription;
use App\Models\PlatformExpense;
use App\Models\SaasPlan;
use App\Models\Member;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class SuperAdminController extends Controller
{
    use ApiResponse;

    // ==========================================
    // 1. SUPER ADMIN AUTHENTICATION
    // ==========================================

    /**
     * Authenticate Super Admin and generate Sanctum Token.
     */
    public function superAdminLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation error', $validator->errors(), 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return $this->errorResponse('Invalid email or password.', [], 401);
            }

            if ($user->role !== 'superadmin') {
                return $this->errorResponse('Access denied. Only Platform Super Admin can access this portal.', [], 403);
            }

            if ($user->status !== 'active') {
                return $this->errorResponse('Your Super Admin account has been deactivated.', [], 403);
            }

            // Create Sanctum Token
            $token = $user->createToken('SuperAdminToken', ['role:superadmin'])->plainTextToken;

            return $this->successResponse('Super Admin Login Successful', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'role' => $user->role,
                    'photo' => $user->photo,
                ],
                'token' => $token,
            ]);

        } catch (Exception $e) {
            Log::error('SuperAdminController@superAdminLogin Exception: ' . $e->getMessage());
            return $this->errorResponse('Something went wrong during Super Admin login.', [], 500);
        }
    }

    // ==========================================
    // 2. DASHBOARD & FINANCIAL METRICS (PROFIT & LOSS)
    // ==========================================

    /**
     * Get Complete SaaS Financials, Expenses, Profit/Loss, and Gym Metrics.
     */
    public function getSuperAdminDashboardStats(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $subQuery = GymSubscription::query();
            $expQuery = PlatformExpense::query();

            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
                $subQuery->whereBetween('start_date', [$start->toDateString(), $end->toDateString()]);
                $expQuery->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()]);
            }

            // 1. Financial Metrics
            $totalSaaSRevenue = (float) $subQuery->where('payment_status', 'paid')->sum('amount_paid');
            $totalPlatformExpenses = (float) $expQuery->sum('amount');
            $netProfit = $totalSaaSRevenue - $totalPlatformExpenses;
            $profitMargin = $totalSaaSRevenue > 0 ? round(($netProfit / $totalSaaSRevenue) * 100, 1) : 0;

            // 2. Gyms Status Count
            $totalGyms = Gym::count();
            $today = Carbon::today();
            $next7Days = Carbon::today()->addDays(7);

            $activeGyms = Gym::where('status', 'active')
                ->where(function($q) use ($today) {
                    $q->whereNull('subscription_end_date')
                      ->orWhere('subscription_end_date', '>=', $today);
                })->count();

            $expiringSoonGyms = Gym::where('status', 'active')
                ->whereNotNull('subscription_end_date')
                ->whereBetween('subscription_end_date', [$today, $next7Days])
                ->count();

            $expiredGyms = Gym::where('status', 'active')
                ->whereNotNull('subscription_end_date')
                ->where('subscription_end_date', '<', $today)
                ->count();

            $suspendedGyms = Gym::where('status', 'suspended')->count();
            $totalPlatformMembers = Member::count();

            // 3. Monthly Trend (Last 6 Months Income vs Expense vs Profit)
            $monthlyTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthDate = Carbon::now()->subMonths($i);
                $monthKey = $monthDate->format('Y-m');
                $monthLabel = $monthDate->format('M Y');

                $monthRev = (float) GymSubscription::where('payment_status', 'paid')
                    ->whereYear('start_date', $monthDate->year)
                    ->whereMonth('start_date', $monthDate->month)
                    ->sum('amount_paid');

                $monthExp = (float) PlatformExpense::whereYear('expense_date', $monthDate->year)
                    ->whereMonth('expense_date', $monthDate->month)
                    ->sum('amount');

                $monthlyTrends[] = [
                    'month' => $monthLabel,
                    'revenue' => $monthRev,
                    'expense' => $monthExp,
                    'profit' => $monthRev - $monthExp,
                ];
            }

            // 4. Category-wise Platform Expenses
            $expenseCategories = PlatformExpense::select('category', DB::raw('SUM(amount) as total_amount'))
                ->groupBy('category')
                ->orderBy('total_amount', 'desc')
                ->get();

            // 5. Recent 5 Subscriptions
            $recentSubscriptions = GymSubscription::with(['gym:id,name,gym_code'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // 6. Recent 5 Expiring Gyms
            $expiringGymsList = Gym::with(['owner:id,name,mobile,email'])
                ->whereNotNull('subscription_end_date')
                ->where('subscription_end_date', '>=', $today)
                ->orderBy('subscription_end_date', 'asc')
                ->limit(5)
                ->get();

            return $this->successResponse('Dashboard data fetched successfully', [
                'financials' => [
                    'total_revenue' => $totalSaaSRevenue,
                    'total_expenses' => $totalPlatformExpenses,
                    'net_profit' => $netProfit,
                    'profit_margin' => $profitMargin,
                    'is_profitable' => $netProfit >= 0,
                ],
                'gym_stats' => [
                    'total_gyms' => $totalGyms,
                    'active_gyms' => $activeGyms,
                    'expiring_soon_gyms' => $expiringSoonGyms,
                    'expired_gyms' => $expiredGyms,
                    'suspended_gyms' => $suspendedGyms,
                    'total_platform_members' => $totalPlatformMembers,
                ],
                'monthly_trends' => $monthlyTrends,
                'expense_categories' => $expenseCategories,
                'recent_subscriptions' => $recentSubscriptions,
                'expiring_gyms_list' => $expiringGymsList,
            ]);

        } catch (Exception $e) {
            Log::error('SuperAdminController@getSuperAdminDashboardStats Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch dashboard stats.', [], 500);
        }
    }

    // ==========================================
    // 3. GYMS / CLIENT MANAGEMENT
    // ==========================================

    /**
     * List all Gym Clients with subscription details and member counts.
     */
    public function listGymClients(Request $request)
    {
        try {
            $search = $request->query('search');
            $status = $request->query('status');
            $perPage = $request->query('per_page', 15);

            $query = Gym::with(['owner:id,name,email,mobile', 'activeSubscription'])
                ->withCount('members');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('gym_code', 'like', "%{$search}%")
                      ->orWhere('contact_number', 'like', "%{$search}%")
                      ->orWhereHas('owner', function ($oq) use ($search) {
                          $oq->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('mobile', 'like', "%{$search}%");
                      });
                });
            }

            if ($status && $status !== 'all') {
                $today = Carbon::today();
                if ($status === 'active') {
                    $query->where('status', 'active')
                          ->where(function($q) use ($today) {
                              $q->whereNull('subscription_end_date')->orWhere('subscription_end_date', '>=', $today);
                          });
                } elseif ($status === 'expired') {
                    $query->whereNotNull('subscription_end_date')
                          ->where('subscription_end_date', '<', $today);
                } elseif ($status === 'expiring') {
                    $next7Days = Carbon::today()->addDays(7);
                    $query->whereBetween('subscription_end_date', [$today, $next7Days]);
                } elseif ($status === 'suspended') {
                    $query->where('status', 'suspended');
                }
            }

            $gyms = $query->orderBy('id', 'desc')->paginate($perPage);

            return $this->successResponse('Gyms list fetched successfully', $gyms);

        } catch (Exception $e) {
            Log::error('SuperAdminController@listGymClients Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch gyms list.', [], 500);
        }
    }

    /**
     * Onboard a new Gym Client and create Owner credentials.
     */
    public function createGymClient(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'gym_name' => 'required|string|max:150',
                'owner_name' => 'required|string|max:100',
                'email' => 'required|email|max:150|unique:users,email',
                'mobile' => 'required|string|size:10|unique:users,mobile',
                'password' => 'required|string|min:6',
                'plan_name' => 'required|string|max:100',
                'billing_cycle' => 'required|string|in:monthly,quarterly,yearly,lifetime,trial',
                'amount_paid' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string',
                'address' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation error', $validator->errors(), 422);
            }

            // Generate unique Gym Code
            $gymCode = 'GYM' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

            // Calculate Subscription Dates
            $startDate = Carbon::today();
            $endDate = match($request->billing_cycle) {
                'monthly' => $startDate->copy()->addMonth(),
                'quarterly' => $startDate->copy()->addMonths(3),
                'yearly' => $startDate->copy()->addYear(),
                'trial' => $startDate->copy()->addDays(14),
                'lifetime' => $startDate->copy()->addYears(50),
                default => $startDate->copy()->addMonth(),
            };

            // 1. Create Gym
            $gym = Gym::create([
                'name' => $request->gym_name,
                'gym_code' => $gymCode,
                'status' => 'active',
                'subscription_end_date' => $endDate,
                'contact_number' => $request->mobile,
                'address' => $request->address,
            ]);

            // 2. Create Owner User
            $owner = User::create([
                'gym_id' => $gym->id,
                'role' => 'owner',
                'name' => $request->owner_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            // Assign owner_id in gym
            $gym->update(['owner_id' => $owner->id]);

            // 3. Record Initial SaaS Subscription
            GymSubscription::create([
                'gym_id' => $gym->id,
                'plan_name' => $request->plan_name,
                'amount_paid' => $request->amount_paid,
                'billing_cycle' => $request->billing_cycle,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_method' => $request->payment_method ?? 'UPI',
                'payment_status' => 'paid',
                'status' => 'active',
                'notes' => 'Initial onboarding subscription',
            ]);

            DB::commit();

            return $this->successResponse('Gym client onboarded successfully!', [
                'gym' => $gym->load('owner'),
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SuperAdminController@createGymClient Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to onboard gym client: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Renew or Extend a Gym's SaaS Subscription.
     */
    public function renewGymSubscription(Request $request, $gymId)
    {
        DB::beginTransaction();
        try {
            $gym = Gym::findOrFail($gymId);

            $validator = Validator::make($request->all(), [
                'plan_name' => 'required|string|max:100',
                'billing_cycle' => 'required|string|in:monthly,quarterly,yearly,lifetime',
                'amount_paid' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation error', $validator->errors(), 422);
            }

            // Calculate start date based on current expiry or today
            $today = Carbon::today();
            $currentExpiry = $gym->subscription_end_date ? Carbon::parse($gym->subscription_end_date) : null;
            $startDate = ($currentExpiry && $currentExpiry->isFuture()) ? $currentExpiry : $today;

            $endDate = match($request->billing_cycle) {
                'monthly' => $startDate->copy()->addMonth(),
                'quarterly' => $startDate->copy()->addMonths(3),
                'yearly' => $startDate->copy()->addYear(),
                'lifetime' => $startDate->copy()->addYears(50),
                default => $startDate->copy()->addMonth(),
            };

            // 1. Mark previous active subscriptions as expired
            GymSubscription::where('gym_id', $gym->id)->where('status', 'active')->update(['status' => 'expired']);

            // 2. Create new subscription entry
            $sub = GymSubscription::create([
                'gym_id' => $gym->id,
                'plan_name' => $request->plan_name,
                'amount_paid' => $request->amount_paid,
                'billing_cycle' => $request->billing_cycle,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'payment_method' => $request->payment_method ?? 'UPI',
                'payment_status' => 'paid',
                'status' => 'active',
                'notes' => $request->notes,
            ]);

            // 3. Update Gym model
            $gym->update([
                'status' => 'active',
                'subscription_end_date' => $endDate,
            ]);

            DB::commit();

            return $this->successResponse('Subscription renewed successfully!', [
                'subscription' => $sub,
                'gym' => $gym,
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('SuperAdminController@renewGymSubscription Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to renew subscription: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Toggle Gym Status (Active <-> Suspended).
     */
    public function toggleGymStatus(Request $request, $gymId)
    {
        try {
            $gym = Gym::findOrFail($gymId);
            $newStatus = $gym->status === 'active' ? 'suspended' : 'active';
            $gym->update(['status' => $newStatus]);

            $message = $newStatus === 'suspended' ? 'Gym has been suspended/locked.' : 'Gym has been activated.';
            return $this->successResponse($message, ['gym' => $gym]);

        } catch (Exception $e) {
            Log::error('SuperAdminController@toggleGymStatus Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to update gym status.', [], 500);
        }
    }

    // ==========================================
    // 4. PLATFORM EXPENSES MANAGEMENT (KHARCHA)
    // ==========================================

    /**
     * List all Platform Expenses with filters.
     */
    public function listPlatformExpenses(Request $request)
    {
        try {
            $search = $request->query('search');
            $category = $request->query('category');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $perPage = $request->query('per_page', 20);

            $query = PlatformExpense::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('vendor_name', 'like', "%{$search}%")
                      ->orWhere('notes', 'like', "%{$search}%");
                });
            }

            if ($category && $category !== 'all') {
                $query->where('category', $category);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('expense_date', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay(),
                ]);
            }

            $totalAmount = (float) (clone $query)->sum('amount');
            $expenses = $query->orderBy('expense_date', 'desc')->paginate($perPage);

            return $this->successResponse('Expenses fetched successfully', [
                'expenses' => $expenses,
                'total_amount' => $totalAmount,
            ]);

        } catch (Exception $e) {
            Log::error('SuperAdminController@listPlatformExpenses Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to fetch expenses.', [], 500);
        }
    }

    /**
     * Record a new Platform Expense (Server, SMS, Domain, Maintenance, etc.).
     */
    public function storePlatformExpense(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:150',
                'category' => 'required|string|max:80',
                'amount' => 'required|numeric|min:0.01',
                'expense_date' => 'required|date',
                'payment_method' => 'nullable|string',
                'vendor_name' => 'nullable|string|max:100',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation error', $validator->errors(), 422);
            }

            $expense = PlatformExpense::create([
                'title' => $request->title,
                'category' => $request->category,
                'amount' => $request->amount,
                'expense_date' => $request->expense_date,
                'payment_method' => $request->payment_method ?? 'UPI',
                'vendor_name' => $request->vendor_name,
                'notes' => $request->notes,
            ]);

            return $this->successResponse('Platform expense recorded successfully!', $expense, 201);

        } catch (Exception $e) {
            Log::error('SuperAdminController@storePlatformExpense Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to record expense.', [], 500);
        }
    }

    /**
     * Delete a Platform Expense record.
     */
    public function deletePlatformExpense($id)
    {
        try {
            $expense = PlatformExpense::findOrFail($id);
            $expense->delete();

            return $this->successResponse('Expense record deleted successfully.');

        } catch (Exception $e) {
            Log::error('SuperAdminController@deletePlatformExpense Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to delete expense record.', [], 500);
        }
    }

    // ==========================================
    // 5. SUPER ADMIN SETTINGS (NAME & PASSWORD)
    // ==========================================

    /**
     * Update Super Admin Name, Email, Mobile and Photo.
     */
    public function updateSuperAdminProfile(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'superadmin') {
                return $this->errorResponse('Unauthorized.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|min:3|max:100',
                'email' => 'required|email|max:150|unique:users,email,' . $user->id,
                'mobile' => 'required|string|size:10|unique:users,mobile,' . $user->id,
                'photo' => 'nullable|image|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation error', $validator->errors(), 422);
            }

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
            ];

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('superadmin_profiles', 'public');
                $updateData['photo'] = '/storage/' . $path;
            }

            $user->update($updateData);

            return $this->successResponse('Profile details updated successfully!', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'photo' => $user->photo,
                    'role' => $user->role,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('SuperAdminController@updateSuperAdminProfile Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to update profile.', [], 500);
        }
    }

    /**
     * Change Super Admin Password.
     */
    public function updateSuperAdminPassword(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->role !== 'superadmin') {
                return $this->errorResponse('Unauthorized.', [], 403);
            }

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation error', $validator->errors(), 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return $this->errorResponse('Current password does not match.', [], 422);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return $this->successResponse('Password updated successfully!');

        } catch (Exception $e) {
            Log::error('SuperAdminController@updateSuperAdminPassword Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to update password.', [], 500);
        }
    }

    // ==========================================
    // 6. EXCEL EXPORT (REPORTS & DIRECTORY)
    // ==========================================

    /**
     * Export All Gyms Client Directory to Clean CSV/Excel.
     */
    public function exportGymsExcel(Request $request)
    {
        try {
            $gyms = Gym::with(['owner', 'activeSubscription'])
                ->withCount('members')
                ->orderBy('id', 'desc')
                ->get();

            $filename = 'flexvora_gyms_directory_' . date('Ymd_His') . '.csv';

            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $columns = [
                'Gym ID', 'Gym Code', 'Gym Name', 'Owner Name', 'Email', 'Mobile', 
                'Address', 'Active Plan', 'Subscription Fee (INR)', 'Start Date', 'Expiry Date', 
                'Total Members', 'Gym Status'
            ];

            $callback = function() use ($gyms, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($gyms as $gym) {
                    $sub = $gym->activeSubscription;
                    fputcsv($file, [
                        $gym->id,
                        $gym->gym_code,
                        $gym->name,
                        $gym->owner ? $gym->owner->name : 'N/A',
                        $gym->owner ? $gym->owner->email : 'N/A',
                        $gym->owner ? $gym->owner->mobile : 'N/A',
                        $gym->address ?? 'N/A',
                        $sub ? $sub->plan_name : 'No Plan',
                        $sub ? number_format($sub->amount_paid, 2) : '0.00',
                        $sub ? $sub->start_date->format('Y-m-d') : 'N/A',
                        $gym->subscription_end_date ? Carbon::parse($gym->subscription_end_date)->format('Y-m-d') : 'Lifetime',
                        $gym->members_count,
                        ucfirst($gym->status),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (Exception $e) {
            Log::error('SuperAdminController@exportGymsExcel Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to export gyms.', [], 500);
        }
    }

    /**
     * Export Platform Financial Profit & Loss Statement to Clean CSV/Excel.
     */
    public function exportPLExcel(Request $request)
    {
        try {
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');

            $subQuery = GymSubscription::with('gym');
            $expQuery = PlatformExpense::query();

            if ($startDate && $endDate) {
                $subQuery->whereBetween('start_date', [$startDate, $endDate]);
                $expQuery->whereBetween('expense_date', [$startDate, $endDate]);
            }

            $subscriptions = $subQuery->where('payment_status', 'paid')->get();
            $expenses = $expQuery->get();

            $filename = 'flexvora_platform_profit_loss_' . date('Ymd_His') . '.csv';

            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $callback = function() use ($subscriptions, $expenses) {
                $file = fopen('php://output', 'w');

                // Header section
                fputcsv($file, ['FLEXVORA SAAS PLATFORM - FINANCIAL P&L STATEMENT']);
                fputcsv($file, ['Generated On: ' . date('d M Y H:i:s')]);
                fputcsv($file, []);

                // Combined Transactions
                fputcsv($file, ['Date', 'Type (Income/Expense)', 'Category / Plan', 'Description / Client', 'Payment Method', 'Income (INR)', 'Expense (INR)']);

                $totalIncome = 0;
                $totalExpense = 0;

                // 1. Add Subscriptions (Income)
                foreach ($subscriptions as $sub) {
                    $income = (float) $sub->amount_paid;
                    $totalIncome += $income;
                    fputcsv($file, [
                        $sub->start_date ? $sub->start_date->format('Y-m-d') : $sub->created_at->format('Y-m-d'),
                        'SaaS Income',
                        $sub->plan_name,
                        $sub->gym ? $sub->gym->name . ' (' . $sub->gym->gym_code . ')' : 'Client',
                        $sub->payment_method,
                        number_format($income, 2),
                        '-',
                    ]);
                }

                // 2. Add Platform Expenses
                foreach ($expenses as $exp) {
                    $expense = (float) $exp->amount;
                    $totalExpense += $expense;
                    fputcsv($file, [
                        $exp->expense_date->format('Y-m-d'),
                        'Platform Expense',
                        $exp->category,
                        $exp->title . ($exp->vendor_name ? ' [' . $exp->vendor_name . ']' : ''),
                        $exp->payment_method,
                        '-',
                        number_format($expense, 2),
                    ]);
                }

                $netProfit = $totalIncome - $totalExpense;

                fputcsv($file, []);
                fputcsv($file, ['TOTAL SUMMARY', '', '', '', '', number_format($totalIncome, 2), number_format($totalExpense, 2)]);
                fputcsv($file, ['NET PROFIT / LOSS', '', '', '', '', ($netProfit >= 0 ? '+' : '-') . ' INR ' . number_format(abs($netProfit), 2), '']);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (Exception $e) {
            Log::error('SuperAdminController@exportPLExcel Exception: ' . $e->getMessage());
            return $this->errorResponse('Failed to export P&L report.', [], 500);
        }
    }
}
