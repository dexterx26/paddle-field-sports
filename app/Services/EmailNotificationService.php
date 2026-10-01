<?php

namespace App\Services;

use App\Mail\RegistrationSuccessfulMail;
use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailNotificationService
{
    /**
     * Determine if outgoing email dispatch is currently enabled.
     *
     * @return bool
     */
    public static function isEnabled(): bool
    {
        return (bool) config('mail.enabled', env('MAIL_ENABLED', false));
    }

    /**
     * Send registration confirmation email to newly registered user.
     * If mail is disabled, it safely bypasses without attempting SMTP connections.
     * When enabled, uses primary mailer (Gmail SMTP) with automatic fallback to log mailer so registration never fails.
     *
     * @param User $user
     * @return bool
     */
    public static function sendRegistrationConfirmation(User $user): bool
    {
        if (!static::isEnabled()) {
            Log::info("Email notifications are currently disabled (MAIL_ENABLED=false). Skipping registration email for '{$user->name}' ({$user->email}).");
            return false;
        }

        if (empty($user->email)) {
            Log::info("Skipping registration confirmation email: User ID {$user->id} has no email address.");
            return false;
        }

        $settings = VenueSetting::getSettings();

        try {
            Mail::to($user->email)->send(new RegistrationSuccessfulMail($user, $settings));
            Log::info("Registration confirmation email dispatched successfully to {$user->email} for user '{$user->name}' (ID: {$user->id}).");
            return true;
        } catch (\Throwable $e) {
            Log::warning("Primary mail dispatch (Gmail SMTP) failed for {$user->email}: " . $e->getMessage() . ". Recording to log mailer as fallback.");

            try {
                Mail::mailer('log')->to($user->email)->send(new RegistrationSuccessfulMail($user, $settings));
                Log::info("Registration confirmation email safely written to storage/logs/laravel.log for {$user->email}.");
            } catch (\Throwable $logEx) {
                Log::error("Registration confirmation email fallback logging failed: " . $logEx->getMessage());
            }

            return false;
        }
    }
}
