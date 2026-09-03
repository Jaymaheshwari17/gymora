<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gym extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'gym_code',
        'logo',
        'contact_number',
        'address',
        'gst_number',
        'instagram_link',
        'facebook_link',
    ];

    /**
     * Get the owner of the gym.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the users associated with the gym.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'gym_id');
    }
}
