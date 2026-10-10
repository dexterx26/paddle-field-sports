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

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $resetUrl;
    public VenueSetting $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $resetUrl, ?VenueSetting $settings = null)
    {
        $this->user = $user;
        $this->resetUrl = $resetUrl;
        $this->settings = $settings ?? VenueSetting::getSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = config('app.name', 'Paddle Field Sports Center');
        $subject = "🔐 Reset Your Password – {$appName}";

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
            view: 'emails.reset-password',
            with: [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
                'settings' => $this->settings,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
