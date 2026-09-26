<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Court extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
        'max_players' => 'integer',
        'court_number' => 'integer',
        'is_active' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function slots()
    {
        return $this->hasMany(BookingSlot::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return '₱' . number_format($this->price_per_hour, 2);
    }

    public function getDisplayImageAttribute(): string
    {
        if ($this->image_path && file_exists(public_path('storage/' . $this->image_path))) {
            return asset('storage/' . $this->image_path);
        }
        return asset('images/court-default.jpg');
    }
}
