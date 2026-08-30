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
        $user = Auth::user();
        $gymId = $user->gym_id;
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        // Get all active members for this gym
        $membersQuery = Member::with(['user', 'plan'])
            ->where('gym_id', $gymId)
            ->where('status', 'active');

        // If logged-in user is a trainer, show all gym members or their assigned members
        $members = $membersQuery->get();

        // Get attendance records for this date
        $attendances = Attendance::where('gym_id', $gymId)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('member_id');

        $result = $members->map(function ($member) use ($attendances) {
            $attendance = $attendances->get($member->id);
            $isPresent = $attendance && $attendance->status === 'P';

            $photo = $member->user ? $member->user->photo : null;
            $photoUrl = $photo ? url($photo) : ('https://ui-avatars.com/api/?name=' . urlencode($member->user->name ?? 'U') . '&background=f3f4f6&color=374151');

            return [
                'id' => $member->id,
                'name' => $member->user->name ?? 'Unknown',
                'plan' => $member->plan->plan_group_name ?? 'No Plan',
                'photo_url' => $photoUrl,
                'status' => $attendance ? $attendance->status : null,
                'is_present' => $isPresent,
                'check_in_time' => $attendance && $attendance->check_in_time ? date('g:i A', strtotime($attendance->check_in_time)) : ($isPresent ? ($attendance->created_at ? $attendance->created_at->format('g:i A') : '10:00 AM') : null),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Mark or toggle attendance for a member
     */
    public function toggleAttendance(Request $request)
    {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'date' => 'required|date',
            'status' => 'nullable|in:P,A',
            'is_present' => 'nullable'
        ]);

        $user = Auth::user();
        $gymId = $user->gym_id;
        $memberId = $request->member_id;
        $date = $request->date;

        // Determine status (P or A) from status or is_present
        if ($request->has('status') && in_array($request->status, ['P', 'A'])) {
            $status = $request->status;
        } elseif ($request->has('is_present')) {
            $status = filter_var($request->is_present, FILTER_VALIDATE_BOOLEAN) ? 'P' : 'A';
        } else {
            $status = 'P';
        }

        $checkInTime = ($status === 'P') ? now()->format('H:i:s') : null;

        $attendance = Attendance::updateOrCreate(
            [
                'member_id' => $memberId,
                'date' => $date,
            ],
            [
                'gym_id' => $gymId,
                'status' => $status,
                'marked_by' => $user->id,
                'check_in_time' => $checkInTime,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'data' => [
                'status' => $status,
                'is_present' => ($status === 'P'),
                'check_in_time' => $checkInTime ? date('g:i A', strtotime($checkInTime)) : null,
            ]
        ]);
    }
}
