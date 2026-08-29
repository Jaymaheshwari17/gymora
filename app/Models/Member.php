<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Member extends Model
{
    protected $fillable = [
        'user_id', 'gym_id', 'batch_id', 'trainer_id', 'plan_id',
        'joining_date', 'plan_amount', 'discount', 'total_amount', 'status'
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function gym(): BelongsTo { return $this->belongsTo(Gym::class); }
    public function batch(): BelongsTo { return $this->belongsTo(Batch::class); }
    public function trainer(): BelongsTo { return $this->belongsTo(User::class, 'trainer_id'); }
    public function plan(): BelongsTo { return $this->belongsTo(Plan::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function attendance(): HasMany { return $this->hasMany(Attendance::class); }
}
