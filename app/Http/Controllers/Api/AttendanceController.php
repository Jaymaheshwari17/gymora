<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Get attendance for members on a specific date
     */
    public function getMembersAttendance(Request $request)
    {
        $gymId = Auth::user()->gym_id;
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        // Get all active members
        $members = Member::with(['user', 'plan'])
            ->where('gym_id', $gymId)
            ->where('status', 'active')
            ->get();

        // Get attendance records for this date
        $attendances = Attendance::where('gym_id', $gymId)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('member_id');

        $result = $members->map(function ($member) use ($attendances) {
            $attendance = $attendances->get($member->id);
            return [
                'id' => $member->id,
                'name' => $member->user->name ?? 'Unknown',
                'plan' => $member->plan->plan_group_name ?? 'No Plan',
                'photo_url' => 'https://ui-avatars.com/api/?name=' . urlencode($member->user->name ?? 'U') . '&background=f3f4f6&color=374151',
                'is_present' => $attendance && $attendance->status === 'P',
                'check_in_time' => $attendance ? Carbon::parse($attendance->check_in_time)->format('h:i A') : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Toggle attendance for a member
     */
    public function toggleAttendance(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'date' => 'required|date',
            'is_present' => 'required|boolean'
        ]);

        $gymId = Auth::user()->gym_id;
        $memberId = $request->member_id;
        $date = $request->date;
        $isPresent = $request->is_present;

        $attendance = Attendance::where('member_id', $memberId)
            ->whereDate('date', $date)
            ->first();

        if ($isPresent) {
            if (!$attendance) {
                Attendance::create([
                    'member_id' => $memberId,
                    'gym_id' => $gymId,
                    'date' => $date,
                    'status' => 'P',
                    'marked_by' => Auth::id(),
                    'check_in_time' => Carbon::now('Asia/Kolkata')->format('H:i:s')
                ]);
            } else {
                $attendance->update([
                    'status' => 'P',
                    'marked_by' => Auth::id(),
                    'check_in_time' => $attendance->check_in_time ?? Carbon::now('Asia/Kolkata')->format('H:i:s')
                ]);
            }
        } else {
            if ($attendance) {
                // If unmarked, delete or mark as absent. We'll delete it to keep DB clean for "unmarked".
                $attendance->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => $isPresent ? 'Attendance marked as present' : 'Attendance removed successfully'
        ]);
    }
}
