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

    public function getEffectiveOpeningTime(): string
    {
        return $this->opening_time ?: '06:00';
    }

    public function getEffectiveClosingTime(): string
    {
        return $this->closing_time ?: '00:00';
    }

    public function getStartHourAttribute(): int
    {
        return (int) substr($this->getEffectiveOpeningTime(), 0, 2);
    }

    public function getEndHourAttribute(): int
    {
        $close = (int) substr($this->getEffectiveClosingTime(), 0, 2);
        return ($close === 0 || $this->getEffectiveClosingTime() === '00:00') ? 24 : $close;
    }

    public function getOperatingHoursLabelAttribute(): string
    {
        $start = \Carbon\Carbon::createFromFormat('H:i', $this->getEffectiveOpeningTime())->format('g:i A');
        $endStr = $this->getEffectiveClosingTime();
        if ($endStr === '00:00' || $endStr === '24:00') {
            $end = '12:00 AM (Midnight)';
        } else {
            $end = \Carbon\Carbon::createFromFormat('H:i', $endStr)->format('g:i A');
        }

        return "{$start} - {$end}";
    }

    public function getTotalOperatingHoursAttribute(): int
    {
        $start = $this->start_hour;
        $end = $this->end_hour;
        return max(0, $end - $start);
    }

    public const DEFAULT_COURT_IMAGES = [
        'courts/court-1.jpg' => '/images/courts/court-1.jpg',
        'courts/court-2.jpg' => '/images/courts/court-2.jpg',
        'courts/court-3.jpg' => '/images/courts/court-3.jpg',
    ];

    public function getDisplayImageAttribute(): string
    {
        if (str_starts_with($this->image_path ?? '', 'http://') || str_starts_with($this->image_path ?? '', 'https://')) {
            return $this->image_path;
        }

        $cleanPath = !empty($this->image_path) ? preg_replace('/^storage\//', '', ltrim($this->image_path, '/')) : '';

        // 1. Check if file exists in public/storage
        if (!empty($cleanPath) && file_exists(public_path('storage/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        // 2. Check if file exists in storage/app/public
        if (!empty($cleanPath) && file_exists(storage_path('app/public/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        // 3. Check direct public path (e.g. public/images/courts/...)
        if (!empty($cleanPath) && file_exists(public_path('images/' . $cleanPath))) {
            return asset('images/' . $cleanPath);
        }
        if (!empty($cleanPath) && file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        // 4. Check court number specific committed image in public/images/courts/
        $num = $this->court_number ?? $this->id ?? 1;
        if (file_exists(public_path("images/courts/court-{$num}.jpg"))) {
            return asset("images/courts/court-{$num}.jpg");
        }
        if (file_exists(storage_path("app/public/courts/court-{$num}.jpg"))) {
            return asset("storage/courts/court-{$num}.jpg");
        }

        // 5. Fallback to court-1.jpg if available
        if (file_exists(public_path('images/courts/court-1.jpg'))) {
            return asset('images/courts/court-1.jpg');
        }

        // 6. Final safety fallback
        return 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1000&q=80';
    }
}
