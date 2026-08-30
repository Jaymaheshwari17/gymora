<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberWorkoutPlan extends Model
{
    public $timestamps = false;
    protected $fillable = ['member_id', 'workout_plan_id', 'assigned_by', 'start_date', 'end_date', 'status'];

    public function member() { return $this->belongsTo(Member::class); }
    public function workoutPlan() { return $this->belongsTo(WorkoutPlan::class); }
    public function assigner() { return $this->belongsTo(User::class, 'assigned_by'); }
}
