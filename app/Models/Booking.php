<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'booking_date' => 'date',
        'held_until' => 'datetime',
        'receipt_uploaded_at' => 'datetime',
        'approved_at' => 'datetime',
        'rate_per_hour' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'total_hours' => 'integer',
        'players_count' => 'integer',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function slots()
    {
        return $this->hasMany(BookingSlot::class);
    }

    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_image_path) {
            return null;
        }
        if (str_starts_with($this->receipt_image_path, 'http')) {
            return $this->receipt_image_path;
        }
        return asset('storage/' . ltrim($this->receipt_image_path, '/'));
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format($this->total_amount, 2);
    }

    public function isHeld(): bool
    {
        return $this->booking_status === 'held' && $this->held_until && now()->lt($this->held_until);
    }

    public function isExpired(): bool
    {
        return $this->booking_status === 'held' && $this->held_until && now()->gte($this->held_until);
    }

    public function getRemainingHoldSecondsAttribute(): int
    {
        if (!$this->isHeld()) {
            return 0;
        }
        return max(0, (int) now()->diffInSeconds($this->held_until, false));
    }
}
