<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    protected $fillable = [
        'gym_id',
        'name',
        'start_time',
        'end_time',
    ];

    /**
     * Get the gym that owns the batch.
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }
}
