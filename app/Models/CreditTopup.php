<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditTopup extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'amount_php' => 'decimal:2',
        'credits' => 'decimal:2',
        'conversion_rate' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($topup) {
            if (empty($topup->reference)) {
                $topup->reference = 'CR-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_image_path) {
            return null;
        }
        if (str_starts_with($this->receipt_image_path, 'http')) {
            return $this->receipt_image_path;
        }
        return route('receipt.credit', $this->id);
    }

    public function getFormattedAmountPhpAttribute(): string
    {
        return '₱' . number_format($this->amount_php, 2);
    }

    public function getFormattedCreditsAttribute(): string
    {
        return number_format($this->credits, 1) . ' ' . ($this->credits == 1 ? 'Credit' : 'Credits');
    }
}
