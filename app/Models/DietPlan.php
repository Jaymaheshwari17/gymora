<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DietPlan extends Model
{
    protected $fillable = ['gym_id', 'title', 'created_by', 'is_template'];

    public function gym() { return $this->belongsTo(Gym::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function meals() { return $this->hasMany(DietPlanMeal::class); }
    public function assignments() { return $this->hasMany(MemberDietPlan::class); }
}
