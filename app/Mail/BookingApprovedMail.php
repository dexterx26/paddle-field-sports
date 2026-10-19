<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\VenueSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public VenueSetting $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, ?VenueSetting $settings = null)
    {
        $this->booking = $booking->loadMissing(['court', 'slots', 'user', 'approver']);
        $this->settings = $settings ?? VenueSetting::getSettings();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $appName = config('app.name', 'Paddle Field Sports Center');
        $venueName = $this->settings->venue_name ?? $appName;
        $courtName = $this->booking->court?->name ?? 'Court';
        $ref = $this->booking->booking_reference;

        $subject = "🎉 Court Reservation Approved: {$courtName} ({$ref}) – {$venueName}";

        return new Envelope(
            from: new Address(
                config('mail.from.address', 'dexter.john.luciano@gmail.com'),
                config('mail.from.name', $venueName)
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
            view: 'emails.booking-approved',
            with: [
                'booking' => $this->booking,
                'court' => $this->booking->court,
                'slots' => $this->booking->slots->sortBy('slot_time'),
                'settings' => $this->settings,
                'trackUrl' => route('booking.track', $this->booking->booking_reference),
                'venueName' => $this->settings->venue_name ?? 'Paddle Field Sports Center',
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
