<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSlot extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'rate_per_hour' => 'decimal:2',
        'held_until' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class)->withTrashed();
    }

    /**
     * Scope to check effectively occupied slots (held, pending_approval, confirmed)
     * For held slots, only consider occupied if held_until is in the future.
     */
    public function scopeOccupied($query)
    {
        return $query->where(function ($q) {
            $q->whereIn('status', ['pending_approval', 'confirmed'])
              ->orWhere(function ($sub) {
                  $sub->where('status', 'held')
                      ->where('held_until', '>', now());
              });
        });
    }
}
