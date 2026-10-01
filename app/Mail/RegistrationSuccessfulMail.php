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

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?VenueSetting $settings = null)
    {
        $this->user = $user;
        $this->settings = $settings ?? VenueSetting::getSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = config('app.name', 'Paddle Field Sports Center');

        return new Envelope(
            from: new Address(
                config('mail.from.address', 'paddlefieldsports@gmail.com'),
                config('mail.from.name', $appName)
            ),
            subject: "🎉 Welcome to {$appName} – Registration Successful!",
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
