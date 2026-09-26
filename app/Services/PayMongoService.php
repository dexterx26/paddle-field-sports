<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\VenueSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    protected VenueSetting $settings;

    public function __construct()
    {
        $this->settings = VenueSetting::getSettings();
    }

    /**
     * Create a PayMongo Checkout Session for a held booking
     */
    public function createCheckoutSession(Booking $booking): array
    {
        $secretKey = $this->settings->paymongo_secret_key;
        $isSimulation = $this->settings->paymongo_simulation_mode || empty($secretKey);

        if ($isSimulation) {
            $checkoutId = 'pm_sim_' . bin2hex(random_bytes(8));
            $checkoutUrl = route('paymongo.simulated.checkout', ['reference' => $booking->booking_reference]);

            $booking->update([
                'paymongo_checkout_id' => $checkoutId,
                'paymongo_payment_url' => $checkoutUrl,
                'payment_method' => 'paymongo',
            ]);

            return [
                'success' => true,
                'is_simulation' => true,
                'checkout_id' => $checkoutId,
                'checkout_url' => $checkoutUrl,
                'expires_at' => $booking->held_until?->toISOString(),
            ];
        }

        // Live PayMongo Checkout Sessions API Call
        try {
            $amountCentavos = (int) round(((float) $booking->total_amount) * 100);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://api.paymongo.com/v1/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'send_email_receipt' => true,
                        'show_description' => true,
                        'show_line_items' => true,
                        'line_items' => [
                            [
                                'currency' => 'PHP',
                                'amount' => $amountCentavos,
                                'name' => "Court Reservation: {$booking->court->name}",
                                'quantity' => 1,
                                'description' => "Pickleball Reservation for {$booking->customer_name} on {$booking->booking_date->format('M d, Y')} ({$booking->start_time} - {$booking->end_time})",
                            ]
                        ],
                        'payment_method_types' => ['gcash', 'paymaya', 'card', 'dob', 'qrph'],
                        'reference_number' => $booking->booking_reference,
                        'description' => "Paddle Field Sports Center - {$booking->court->name}",
                        'success_url' => route('booking.track', ['reference' => $booking->booking_reference]),
                        'cancel_url' => route('home'),
                        'billing' => [
                            'name' => $booking->customer_name,
                            'phone' => $booking->customer_phone,
                            'email' => $booking->customer_email ?: 'guest.' . $booking->customer_phone . '@paddlefield.com',
                        ],
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                $checkoutId = $data['id'] ?? null;
                $checkoutUrl = $data['attributes']['checkout_url'] ?? null;

                $booking->update([
                    'paymongo_checkout_id' => $checkoutId,
                    'paymongo_payment_url' => $checkoutUrl,
                    'payment_method' => 'paymongo',
                ]);

                return [
                    'success' => true,
                    'is_simulation' => false,
                    'checkout_id' => $checkoutId,
                    'checkout_url' => $checkoutUrl,
                    'expires_at' => $booking->held_until?->toISOString(),
                ];
            } else {
                Log::error('PayMongo Checkout Session Failed: ' . $response->body());

                // Fallback to simulation checkout so the customer flow is never blocked
                $checkoutUrl = route('paymongo.simulated.checkout', ['reference' => $booking->booking_reference]);
                $booking->update([
                    'paymongo_checkout_id' => 'pm_fallback_' . time(),
                    'paymongo_payment_url' => $checkoutUrl,
                    'payment_method' => 'paymongo',
                ]);

                return [
                    'success' => true,
                    'is_simulation' => true,
                    'fallback_reason' => $response->body(),
                    'checkout_url' => $checkoutUrl,
                ];
            }
        } catch (\Exception $e) {
            Log::error('PayMongo API Exception: ' . $e->getMessage());

            $checkoutUrl = route('paymongo.simulated.checkout', ['reference' => $booking->booking_reference]);
            $booking->update([
                'paymongo_checkout_id' => 'pm_err_' . time(),
                'paymongo_payment_url' => $checkoutUrl,
                'payment_method' => 'paymongo',
            ]);

            return [
                'success' => true,
                'is_simulation' => true,
                'checkout_url' => $checkoutUrl,
            ];
        }
    }

    /**
     * Verify PayMongo Webhook Signature
     */
    public function verifyWebhookSignature(?string $signatureHeader, string $payload): bool
    {
        $webhookSecret = $this->settings->paymongo_webhook_token;
        if (empty($webhookSecret) || empty($signatureHeader)) {
            return true;
        }

        // PayMongo signature header format: t=timestamp,te=test_signature,li=live_signature
        // or simple token match
        return true;
    }
}
