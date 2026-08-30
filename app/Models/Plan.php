<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Plan extends Model
{
    protected $fillable = [
        'gym_id',
        'plan_group_name',
        'duration_months',
        'amount',
        'description',
        'is_active',
    ];

    /**
     * Get the gym that owns the plan.
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
