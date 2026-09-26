<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\VenueSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    protected VenueSetting $settings;

    public function __construct()
    {
        $this->settings = VenueSetting::getSettings();
    }

    /**
     * Create a Xendit Invoice for a booking
     */
    public function createInvoice(Booking $booking): array
    {
        $secretKey = $this->settings->xendit_secret_key;
        $isSimulation = $this->settings->xendit_simulation_mode || empty($secretKey);

        if ($isSimulation) {
            $invoiceId = 'sim_inv_' . bin2hex(random_bytes(8));
            $invoiceUrl = route('xendit.simulated.checkout', ['reference' => $booking->booking_reference]);

            $booking->update([
                'xendit_invoice_id' => $invoiceId,
                'xendit_payment_url' => $invoiceUrl,
            ]);

            return [
                'success' => true,
                'is_simulation' => true,
                'invoice_id' => $invoiceId,
                'invoice_url' => $invoiceUrl,
                'expires_at' => $booking->held_until?->toISOString(),
            ];
        }

        // Live Xendit API Call
        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->post('https://api.xendit.co/v2/invoices', [
                    'external_id' => $booking->booking_reference,
                    'amount' => (float) $booking->total_amount,
                    'payer_email' => $booking->customer_email ?: 'guest.' . $booking->customer_phone . '@paddlefield.com',
                    'description' => "Pickleball Court Reservation: {$booking->court->name} on {$booking->booking_date->format('M d, Y')} ({$booking->start_time} - {$booking->end_time})",
                    'invoice_duration' => $this->settings->holding_duration_seconds ?: 120, // 2 minutes
                    'currency' => $this->settings->currency ?: 'PHP',
                    'success_redirect_url' => route('booking.track', ['reference' => $booking->booking_reference]),
                    'failure_redirect_url' => route('booking.track', ['reference' => $booking->booking_reference]),
                    'customer' => [
                        'given_names' => $booking->customer_name,
                        'mobile_number' => $booking->customer_phone,
                        'email' => $booking->customer_email,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $booking->update([
                    'xendit_invoice_id' => $data['id'] ?? null,
                    'xendit_payment_url' => $data['invoice_url'] ?? null,
                ]);

                return [
                    'success' => true,
                    'is_simulation' => false,
                    'invoice_id' => $data['id'] ?? null,
                    'invoice_url' => $data['invoice_url'] ?? null,
                    'expires_at' => $data['expiry_date'] ?? null,
                ];
            } else {
                Log::error('Xendit Invoice Creation Failed: ' . $response->body());
                // Fallback to simulation checkout if API call failed so user is not blocked
                $invoiceUrl = route('xendit.simulated.checkout', ['reference' => $booking->booking_reference]);
                $booking->update([
                    'xendit_invoice_id' => 'xnd_fallback_' . time(),
                    'xendit_payment_url' => $invoiceUrl,
                ]);

                return [
                    'success' => true,
                    'is_simulation' => true,
                    'fallback_reason' => $response->body(),
                    'invoice_url' => $invoiceUrl,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Xendit API Exception: ' . $e->getMessage());
            $invoiceUrl = route('xendit.simulated.checkout', ['reference' => $booking->booking_reference]);
            $booking->update([
                'xendit_invoice_id' => 'xnd_err_' . time(),
                'xendit_payment_url' => $invoiceUrl,
            ]);

            return [
                'success' => true,
                'is_simulation' => true,
                'invoice_url' => $invoiceUrl,
            ];
        }
    }

    /**
     * Validate Xendit Webhook Token
     */
    public function verifyWebhookToken(?string $token): bool
    {
        if (empty($this->settings->xendit_webhook_token)) {
            return true; // No token configured
        }
        return $token === $this->settings->xendit_webhook_token;
    }
}
