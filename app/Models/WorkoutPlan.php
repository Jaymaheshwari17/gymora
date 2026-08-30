<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutPlan extends Model
{
    protected $fillable = ['gym_id', 'title', 'created_by', 'is_template'];

    public function gym() { return $this->belongsTo(Gym::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function days() { return $this->hasMany(WorkoutPlanDay::class); }
    public function assignments() { return $this->hasMany(MemberWorkoutPlan::class); }
}
