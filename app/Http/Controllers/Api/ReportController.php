<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Expense;
use App\Models\Attendance;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function getSummary(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;
            $range = $request->query('range', 'week');
            $timezone = 'Asia/Kolkata';

            $data = $this->aggregateData($gymId, $range, $timezone);
            
            if ($request->query('export') === 'true') {
                return $this->exportCsv($data, $request->query('type', 'all'), $range);
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('ReportController@getSummary: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate report'], 500);
        }
    }

    private function aggregateData($gymId, $range, $timezone)
    {
        $now = Carbon::now($timezone);
        
        switch ($range) {
            case 'month':
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
            case 'quarter':
                $startDate = $now->copy()->firstOfQuarter();
                $endDate = $now->copy()->lastOfQuarter();
                break;
            case 'year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            case 'week':
            default:
                $startDate = $now->copy()->startOfWeek();
                $endDate = $now->copy()->endOfWeek();
                break;
        }

        // Fetch Data
        $payments = Payment::with('member.plan')->where('gym_id', $gymId)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();
            
        $members = Member::where('gym_id', $gymId)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();
            
        $expenses = Expense::where('gym_id', $gymId)
            ->whereBetween('expense_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
            
        $attendances = Attendance::where('gym_id', $gymId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        // 1. Top Stats
        $totalPayments = $payments->sum('paid_amount');
        $totalSales = $payments->sum('total_amount');
        $newMembersCount = $members->count();
        $totalExpenses = $expenses->sum('amount');
        
        $totalAttendanceRecords = $attendances->count();
        $presentCount = $attendances->where('status', 'P')->count();
        $attendanceRate = $totalAttendanceRecords > 0 ? round(($presentCount / $totalAttendanceRecords) * 100, 1) : 0;

        $topStats = [
            'total_payments' => $totalPayments,
            'total_sales' => $totalSales,
            'new_members' => $newMembersCount,
            'attendance_rate' => $attendanceRate,
            'total_expenses' => $totalExpenses
        ];

        // 2. Time-series Data Setup
        $timeseries = [];
        $currentDate = $startDate->copy();
        
        if ($range === 'week' || $range === 'month') {
            while ($currentDate->lte($endDate)) {
                $key = $currentDate->format('Y-m-d');
                $label = $currentDate->format('d M Y');
                $timeseries[$key] = $this->emptyBucket($label);
                $currentDate->addDay();
            }
        } elseif ($range === 'quarter' || $range === 'year') {
            while ($currentDate->lte($endDate)) {
                $key = $currentDate->format('Y-m');
                $label = $currentDate->format('M Y');
                $timeseries[$key] = $this->emptyBucket($label);
                $currentDate->addMonth();
            }
        }

        // Fill Time-series
        foreach ($payments as $p) {
            $date = Carbon::parse($p->created_at)->timezone($timezone);
            $key = ($range === 'week' || $range === 'month') ? $date->format('Y-m-d') : $date->format('Y-m');
            
            if (isset($timeseries[$key])) {
                $timeseries[$key]['collected'] += (float)$p->paid_amount;
                $timeseries[$key]['total_sales'] += (float)$p->total_amount;
                // Assuming all sales are plan sales for now, no other sales.
                $timeseries[$key]['plan_sales'] += (float)$p->total_amount;
                $timeseries[$key]['other_sales'] += 0; 
                // Mock refunds to 0
                $timeseries[$key]['refunds'] += 0;
                $timeseries[$key]['net_payments'] += (float)$p->paid_amount;
            }
        }
        
        foreach ($members as $m) {
            $date = Carbon::parse($m->created_at)->timezone($timezone);
            $key = ($range === 'week' || $range === 'month') ? $date->format('Y-m-d') : $date->format('Y-m');
            if (isset($timeseries[$key])) {
                $timeseries[$key]['new_members']++;
            }
        }

        // 3. Aggregate Expenses by Category
        $expensesSummary = [];
        foreach ($expenses as $e) {
            $cat = $e->category ?: 'Other';
            if (!isset($expensesSummary[$cat])) {
                $expensesSummary[$cat] = 0;
            }
            $expensesSummary[$cat] += (float)$e->amount;
        }
        
        $expensesFormatted = [];
        foreach ($expensesSummary as $cat => $amount) {
            $expensesFormatted[] = ['category' => $cat, 'amount' => $amount];
        }

        // 4. Sales by Plan
        $salesByPlan = [];
        foreach ($payments as $p) {
            if ($p->member && $p->member->plan) {
                $planName = $p->member->plan->plan_group_name . ' (' . $p->member->plan->duration_months . 'M)';
                if (!isset($salesByPlan[$planName])) {
                    $salesByPlan[$planName] = ['members' => 0, 'sales' => 0];
                }
                $salesByPlan[$planName]['members'] += 1;
                $salesByPlan[$planName]['sales'] += (float)$p->total_amount;
            }
        }
        
        $salesByPlanFormatted = [];
        foreach ($salesByPlan as $planName => $d) {
            $salesByPlanFormatted[] = ['plan_name' => $planName, 'members' => $d['members'], 'sales' => $d['sales']];
        }

        return [
            'top_stats' => $topStats,
            'timeseries' => array_values($timeseries),
            'expenses_summary' => $expensesFormatted,
            'sales_by_plan' => $salesByPlanFormatted
        ];
    }

    private function emptyBucket($label) {
        return [
            'date' => $label,
            'collected' => 0,
            'refunds' => 0,
            'net_payments' => 0,
            'total_sales' => 0,
            'plan_sales' => 0,
            'other_sales' => 0,
            'new_members' => 0
        ];
    }

    private function exportCsv($data, $type, $range)
    {
        $filename = "report_{$type}_{$range}_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data, $type) {
            $file = fopen('php://output', 'w');
            
            if ($type === 'all') {
                fputcsv($file, ['=== TOP STATS ===']);
                fputcsv($file, ['Total Payments', 'Total Sales', 'New Members', 'Attendance Rate', 'Total Expenses']);
                fputcsv($file, [$data['top_stats']['total_payments'], $data['top_stats']['total_sales'], $data['top_stats']['new_members'], $data['top_stats']['attendance_rate'].'%', $data['top_stats']['total_expenses']]);
                
                fputcsv($file, []);
                fputcsv($file, ['=== PAYMENTS ===']);
                fputcsv($file, ['Date', 'Collected Payments', 'Refunds', 'Net Payments']);
                foreach ($data['timeseries'] as $row) {
                    fputcsv($file, [$row['date'], $row['collected'], $row['refunds'], $row['net_payments']]);
                }
                
                fputcsv($file, []);
                fputcsv($file, ['=== SALES ===']);
                fputcsv($file, ['Date', 'Total Sales', 'Plan Sales', 'Other Sales']);
                foreach ($data['timeseries'] as $row) {
                    fputcsv($file, [$row['date'], $row['total_sales'], $row['plan_sales'], $row['other_sales']]);
                }
                
                fputcsv($file, []);
                fputcsv($file, ['=== NEW MEMBERS ===']);
                fputcsv($file, ['Date', 'New Members']);
                foreach ($data['timeseries'] as $row) {
                    fputcsv($file, [$row['date'], $row['new_members']]);
                }
            } elseif ($type === 'payments') {
                fputcsv($file, ['Date', 'Collected Payments', 'Refunds', 'Net Payments']);
                foreach ($data['timeseries'] as $row) {
                    fputcsv($file, [$row['date'], $row['collected'], $row['refunds'], $row['net_payments']]);
                }
            } elseif ($type === 'sales') {
                fputcsv($file, ['Date', 'Total Sales', 'Plan Sales', 'Other Sales']);
                foreach ($data['timeseries'] as $row) {
                    fputcsv($file, [$row['date'], $row['total_sales'], $row['plan_sales'], $row['other_sales']]);
                }
            } elseif ($type === 'members') {
                fputcsv($file, ['Date', 'New Members']);
                foreach ($data['timeseries'] as $row) {
                    fputcsv($file, [$row['date'], $row['new_members']]);
                }
            } elseif ($type === 'expenses') {
                fputcsv($file, ['Category', 'Amount']);
                foreach ($data['expenses_summary'] as $row) {
                    fputcsv($file, [$row['category'], $row['amount']]);
                }
            } elseif ($type === 'plans') {
                fputcsv($file, ['Plan Name', 'Members', 'Sales']);
                foreach ($data['sales_by_plan'] as $row) {
                    fputcsv($file, [$row['plan_name'], $row['members'], $row['sales']]);
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
