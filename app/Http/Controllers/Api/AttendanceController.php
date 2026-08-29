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
            $photo = $member->user->photo;
            $photoUrl = $photo ? url($photo) : 'https://ui-avatars.com/api/?name=' . urlencode($member->user->name ?? 'U') . '&background=f3f4f6&color=374151';

            return [
                'id' => $member->id,
                'name' => $member->user->name ?? 'Unknown',
                'plan' => $member->plan->plan_group_name ?? 'No Plan',
                'photo_url' => $photoUrl,
                'status' => $attendance ? $attendance->status : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    public function toggleAttendance(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'date' => 'required|date',
            'status' => 'required|in:P,A'
        ]);

        $gymId = Auth::user()->gym_id;
        $memberId = $request->member_id;
        $date = $request->date;
        $status = $request->status;

        $attendance = Attendance::where('member_id', $memberId)
            ->whereDate('date', $date)
            ->first();

        if (!$attendance) {
            Attendance::create([
                'member_id' => $memberId,
                'gym_id' => $gymId,
                'date' => $date,
                'status' => $status,
                'marked_by' => Auth::id()
            ]);
        } else {
            $attendance->update([
                'status' => $status,
                'marked_by' => Auth::id()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully'
        ]);
    }
}
