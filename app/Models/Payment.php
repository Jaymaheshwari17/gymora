<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'member_id', 'gym_id', 'total_amount', 'paid_amount',
        'due_amount', 'payment_date', 'status'
    ];

    public function member(): BelongsTo { return $this->belongsTo(Member::class); }
    public function gym(): BelongsTo { return $this->belongsTo(Gym::class); }
    public function transactions(): HasMany { return $this->hasMany(PaymentTransaction::class)->orderBy('payment_date', 'asc')->orderBy('id', 'asc'); }
}
