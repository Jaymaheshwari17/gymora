<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberDietPlan extends Model
{
    public $timestamps = false;
    protected $fillable = ['member_id', 'diet_plan_id', 'assigned_by', 'start_date', 'end_date', 'status'];

    public function member() { return $this->belongsTo(Member::class); }
    public function dietPlan() { return $this->belongsTo(DietPlan::class); }
    public function assigner() { return $this->belongsTo(User::class, 'assigned_by'); }
}
