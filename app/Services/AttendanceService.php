<?php

namespace App\Services;

use App\Models\Attendance;
use Exception;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    /**
     * Mark attendance for a member.
     */
    public function markAttendance(int $gymId, int $memberId, int $markedBy, string $status, ?string $checkInTime = null)
    {
        try {
            $date = now()->toDateString();
            
            return Attendance::updateOrCreate(
                ['member_id' => $memberId, 'date' => $date, 'gym_id' => $gymId],
                ['status' => $status, 'marked_by' => $markedBy, 'check_in_time' => $checkInTime]
            );
        } catch (Exception $e) {
            Log::error('AttendanceService@markAttendance Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
