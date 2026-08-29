<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Member;
use App\Models\Plan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function getSummary(Request $request)
    {
        try {
            $gymId = $request->user()->gym_id;
            $range = $request->query('range', 'week'); // week, month, quarter, year
            $timezone = 'Asia/Kolkata'; // Assuming IST as per earlier context

            $data = $this->aggregateData($gymId, $range, $timezone);
            
            if ($request->query('export') === 'true') {
                return $this->exportCsv($data, $range);
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
                $startDate = $now->copy()->startOfWeek(); // default starts on Monday
                $endDate = $now->copy()->endOfWeek();
                break;
        }

        // Fetch Raw Data within timeframe
        $payments = Payment::with('member.user')
            ->where('gym_id', $gymId)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();
            
        $members = Member::where('gym_id', $gymId)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        // Initialize buckets
        $buckets = [];
        
        if ($range === 'week') {
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            foreach ($days as $day) {
                $buckets[$day] = ['label' => $day, 'collected' => 0, 'sell' => 0, 'new_members' => 0, 'breakdown' => []];
            }
        } elseif ($range === 'month') {
            $daysInMonth = $now->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $buckets[(string)$i] = ['label' => (string)$i, 'collected' => 0, 'sell' => 0, 'new_members' => 0, 'breakdown' => []];
            }
        } elseif ($range === 'quarter') {
            $months = [];
            for ($i = 0; $i < 3; $i++) {
                $m = $startDate->copy()->addMonths($i)->format('M'); // Jan, Feb, etc.
                $buckets[$m] = ['label' => $m, 'collected' => 0, 'sell' => 0, 'new_members' => 0, 'breakdown' => []];
            }
        } elseif ($range === 'year') {
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            foreach ($months as $m) {
                $buckets[$m] = ['label' => $m, 'collected' => 0, 'sell' => 0, 'new_members' => 0, 'breakdown' => []];
            }
        }

        // Aggregate Payments
        foreach ($payments as $p) {
            $date = Carbon::parse($p->created_at)->timezone($timezone);
            $key = $this->getBucketKey($date, $range);
            
            if (isset($buckets[$key])) {
                $buckets[$key]['collected'] += (float)$p->paid_amount;
                $buckets[$key]['sell'] += (float)$p->total_amount;
                
                $buckets[$key]['breakdown'][] = [
                    'name' => $p->member->user->name ?? 'Unknown',
                    'mobile' => $p->member->user->mobile ?? 'N/A',
                    'paid' => (float)$p->paid_amount,
                    'total' => (float)$p->total_amount,
                    'date' => $date->format('d M Y, h:i A')
                ];
            }
        }

        // Aggregate Members
        foreach ($members as $m) {
            $date = Carbon::parse($m->created_at)->timezone($timezone);
            $key = $this->getBucketKey($date, $range);
            
            if (isset($buckets[$key])) {
                $buckets[$key]['new_members']++;
            }
        }

        return array_values($buckets);
    }

    private function getBucketKey($date, $range)
    {
        if ($range === 'week') return $date->format('D'); // Mon, Tue...
        if ($range === 'month') return $date->format('j'); // 1, 2, 3...
        return $date->format('M'); // Jan, Feb... (for Quarter and Year)
    }

    private function exportCsv($data, $range)
    {
        $filename = "report_{$range}_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($data, $range) {
            $file = fopen('php://output', 'w');
            
            // Write Headers
            fputcsv($file, ['Period', 'Collected Payments', 'Total Sell', 'New Members']);
            
            // Write Data
            foreach ($data as $row) {
                fputcsv($file, [
                    $row['label'],
                    $row['collected'],
                    $row['sell'],
                    $row['new_members']
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
