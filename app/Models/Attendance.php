<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $table = 'attendance';
    
    protected $fillable = [
        'member_id', 'gym_id', 'date', 'status', 'marked_by', 'check_in_time'
    ];

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function gym(): BelongsTo { return $this->belongsTo(Gym::class); }
    public function marker(): BelongsTo { return $this->belongsTo(User::class, 'marked_by'); }
}
