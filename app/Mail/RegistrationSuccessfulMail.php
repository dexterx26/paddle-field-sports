<?php

namespace App\Mail;

use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationSuccessfulMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public VenueSetting $settings;
    public ?string $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?VenueSetting $settings = null, ?string $verificationUrl = null)
    {
        $this->user = $user;
        $this->settings = $settings ?? VenueSetting::getSettings();
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = config('app.name', 'Paddle Field Sports Center');
        $subject = $this->verificationUrl && !$this->user->hasVerifiedEmail()
            ? "✉️ Please Confirm Your Email – Welcome to {$appName}!"
            : "🎉 Welcome to {$appName} – Registration Successful!";

        return new Envelope(
            from: new Address(
                config('mail.from.address', 'paddlefieldsports@gmail.com'),
                config('mail.from.name', $appName)
            ),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-successful',
            with: [
                'user' => $this->user,
                'settings' => $this->settings,
                'verificationUrl' => $this->verificationUrl,
                'loginUrl' => route('login'),
                'reserveUrl' => route('home') . '#booking-engine',
                'profileUrl' => route('profile.edit'),
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
