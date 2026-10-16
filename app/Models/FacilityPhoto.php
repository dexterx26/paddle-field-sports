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
        'gallery/court-action.jpg' => '/images/gallery/court-action.jpg',
        'gallery/club-lounge.jpg' => '/images/gallery/club-lounge.jpg',
        'gallery/entrance-sign.jpg' => '/images/gallery/entrance-sign.jpg',
        'gallery/outdoor-sunset.jpg' => '/images/gallery/court-action.jpg',
    ];

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->image_path ?? '', 'http://') || str_starts_with($this->image_path ?? '', 'https://')) {
            return $this->image_path;
        }

        $cleanPath = !empty($this->image_path) ? preg_replace('/^storage\//', '', ltrim($this->image_path, '/')) : '';

        // Check if the file exists locally in public/storage or storage/app/public
        if (!empty($cleanPath) && (file_exists(public_path('storage/' . $cleanPath)) || file_exists(storage_path('app/public/' . $cleanPath)))) {
            return asset('storage/' . $cleanPath);
        }

        // Check public/images
        if (!empty($cleanPath) && file_exists(public_path('images/' . $cleanPath))) {
            return asset('images/' . $cleanPath);
        }

        if (isset(self::DEFAULTS[$cleanPath]) && file_exists(public_path(ltrim(self::DEFAULTS[$cleanPath], '/')))) {
            return asset(ltrim(self::DEFAULTS[$cleanPath], '/'));
        }

        if (file_exists(public_path('images/gallery/court-action.jpg'))) {
            return asset('images/gallery/court-action.jpg');
        }

        return 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=1200&q=80';
    }
}
