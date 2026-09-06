<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaasPlan extends Model
{
    use HasFactory;

    protected $table = 'saas_plans';

    protected $fillable = [
        'name',
        'code',
        'price',
        'billing_cycle',
        'max_members',
        'description',
        'features',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'max_members' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(GymSubscription::class, 'saas_plan_id');
    }
}
