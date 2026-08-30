<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'gym_id',
        'title',
        'amount',
        'expense_date',
        'category',
        'description',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }
}
