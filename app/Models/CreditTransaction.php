<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount_credits' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedCreditsAttribute(): string
    {
        $prefix = $this->amount_credits > 0 ? '+' : '';
        return $prefix . number_format($this->amount_credits, 1) . ' Credits';
    }
}
