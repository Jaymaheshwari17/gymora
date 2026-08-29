<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutPlanDay extends Model
{
    public $timestamps = false;
    protected $fillable = ['workout_plan_id', 'day_label', 'exercises'];
    protected $casts = ['exercises' => 'array'];
    
    public function workoutPlan() { return $this->belongsTo(WorkoutPlan::class); }
}
