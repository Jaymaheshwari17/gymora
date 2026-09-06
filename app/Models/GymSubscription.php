<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymSubscription extends Model
{
    use HasFactory;

    protected $table = 'gym_subscriptions';

    protected $fillable = [
        'gym_id',
        'saas_plan_id',
        'plan_name',
        'amount_paid',
        'start_date',
        'end_date',
        'billing_cycle',
        'payment_method',
        'payment_status',
        'status',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class, 'gym_id');
    }

    public function saasPlan()
    {
        return $this->belongsTo(SaasPlan::class, 'saas_plan_id');
    }
}
