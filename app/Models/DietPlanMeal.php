<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DietPlanMeal extends Model
{
    public $timestamps = false;
    protected $fillable = ['diet_plan_id', 'meal_type', 'food_items', 'calories'];
    
    public function dietPlan() { return $this->belongsTo(DietPlan::class); }
}
