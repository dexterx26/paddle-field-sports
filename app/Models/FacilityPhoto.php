<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityPhoto extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Default fallback photos for seeded items or missing files
     */
    public const DEFAULTS = [
        'gallery/court-action.jpg' => 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1200&q=80',
        'gallery/club-lounge.jpg' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=80',
        'gallery/outdoor-sunset.jpg' => 'https://images.unsplash.com/photo-1563299796-17596ed6b017?auto=format&fit=crop&w=1200&q=80',
    ];

    public function getUrlAttribute(): string
    {
        if (empty($this->image_path)) {
            return self::DEFAULTS['gallery/court-action.jpg'];
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        $cleanPath = preg_replace('/^storage\//', '', ltrim($this->image_path, '/'));

        // Check if the file exists locally in public/storage or storage/app/public
        $existsLocally = file_exists(public_path('storage/' . $cleanPath)) || 
                         file_exists(storage_path('app/public/' . $cleanPath));

        if (!$existsLocally && isset(self::DEFAULTS[$cleanPath])) {
            return self::DEFAULTS[$cleanPath];
        }

        if (str_starts_with($cleanPath, 'storage/')) {
            return asset($cleanPath);
        }

        return asset('storage/' . $cleanPath);
    }
}
