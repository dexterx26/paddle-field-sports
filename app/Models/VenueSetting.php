<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'xendit_simulation_mode' => 'boolean',
        'paymongo_simulation_mode' => 'boolean',
        'holding_duration_seconds' => 'integer',
        'credit_conversion_rate' => 'decimal:2',
        'credits_enabled' => 'boolean',
    ];

    /**
     * Get the single global settings instance
     */
    public static function getSettings(): self
    {
        $settings = static::first();
        if ($settings) {
            return $settings;
        }

        $defaultData = [
            'venue_name' => 'Paddle Field Sports Center',
            'tagline' => 'Premier Indoor Pickleball Facility',
            'description' => 'Experience world-class pickleball action at Paddle Field Sports Center. Equipped with tournament-grade cushioned acrylic courts, high-powered LED lighting, and premium club amenities.',
            'phone' => '+63 917 555 7233',
            'email' => 'contact@paddlefield.com',
            'address' => 'Bacal 3, Talavera, Nueva Ecija',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'credit_conversion_rate' => 150.00,
            'credits_enabled' => true,
            'xendit_simulation_mode' => true,
            'paymongo_simulation_mode' => true,
            'manual_bank_name' => 'GCash / Maya / BDO Bank',
            'manual_account_name' => 'Paddle Field Sports Center Inc.',
            'manual_account_number' => '0917-555-7233',
            'manual_payment_instructions' => "1. Transfer the exact reservation amount via GCash, Maya, or BDO.\n2. Write your Booking Reference code in the transfer notes.\n3. Take a screenshot of the completed transaction receipt.\n4. Upload the screenshot below to secure your court reservation.",
            'holding_duration_seconds' => 120, // 2 minutes hold for Xendit / PayMongo checkout
        ];

        try {
            $tableColumns = \Illuminate\Support\Facades\Schema::getColumnListing('venue_settings');
            if (!empty($tableColumns)) {
                $defaultData = array_intersect_key($defaultData, array_flip($tableColumns));
            }
        } catch (\Throwable $e) {}

        return static::create($defaultData);
    }

    /*
    |--------------------------------------------------------------------------
    | RA 10173 Section 20(c): Encryption of API Credentials & Secrets at Rest
    |--------------------------------------------------------------------------
    */

    public function getPaymongoSecretKeyAttribute($value)
    {
        if (empty($value)) return $value;
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function setPaymongoSecretKeyAttribute($value)
    {
        $this->attributes['paymongo_secret_key'] = !empty($value) ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null;
    }

    public function getXenditSecretKeyAttribute($value)
    {
        if (empty($value)) return $value;
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function setXenditSecretKeyAttribute($value)
    {
        $this->attributes['xendit_secret_key'] = !empty($value) ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null;
    }

    public function getPaymongoWebhookTokenAttribute($value)
    {
        if (empty($value)) return $value;
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function setPaymongoWebhookTokenAttribute($value)
    {
        $this->attributes['paymongo_webhook_token'] = !empty($value) ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null;
    }

    public function getXenditWebhookTokenAttribute($value)
    {
        if (empty($value)) return $value;
        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable $e) {
            return $value;
        }
    }

    public function setXenditWebhookTokenAttribute($value)
    {
        $this->attributes['xendit_webhook_token'] = !empty($value) ? \Illuminate\Support\Facades\Crypt::encryptString($value) : null;
    }

    /**
     * Get the public URL for the uploaded venue logo, if set.
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!empty($this->logo_path)) {
            if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
                return $this->logo_path;
            }
            return asset('storage/' . ltrim($this->logo_path, '/'));
        }

        return null;
    }

    /**
     * Check if a custom venue logo has been uploaded.
     */
    public function getHasCustomLogoAttribute(): bool
    {
        return !empty($this->logo_path);
    }

    /**
     * Get the icon URL for browser tab favicon and branding.
     * Falls back to the default pickleball vector icon if no custom logo is uploaded.
     */
    public function getTabIconUrlAttribute(): string
    {
        return $this->logo_url ?: asset('images/default-logo.svg');
    }

    /**
     * Get the absolute local file path for embedding the logo in emails via CID attachment.
     * Prioritizes custom uploaded logo (if non-SVG image), falling back to default branded PNG.
     */
    public function getEmailLogoPath(): ?string
    {
        if (!empty($this->logo_path)) {
            $diskPath = storage_path('app/public/' . ltrim($this->logo_path, '/'));
            if (file_exists($diskPath) && !str_ends_with(strtolower($diskPath), '.svg')) {
                return $diskPath;
            }
        }

        $defaultPng = public_path('images/default-logo.png');
        if (file_exists($defaultPng)) {
            return $defaultPng;
        }

        return null;
    }
}
