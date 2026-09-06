<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformExpense extends Model
{
    use HasFactory;

    protected $table = 'platform_expenses';

    protected $fillable = [
        'title',
        'category',
        'amount',
        'expense_date',
        'payment_method',
        'vendor_name',
        'receipt_url',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];
}
