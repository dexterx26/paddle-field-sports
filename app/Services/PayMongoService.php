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
        $secretKey = $this->settings->paymongo_secret_key ?: config('services.paymongo.secret_key');
        $isSimulation = $this->settings->paymongo_simulation_mode || empty($secretKey);

        if ($isSimulation) {
            $checkoutId = 'pm_sim_' . bin2hex(random_bytes(8));
            $checkoutUrl = route('paymongo.simulated.checkout', ['reference' => $booking->booking_reference]);

            $booking->update([
                'paymongo_checkout_id' => $checkoutId,
                'paymongo_payment_url' => $checkoutUrl,
                'payment_method' => 'paymongo',
            ]);

            $holdingSeconds = (int) ($this->settings->holding_duration_seconds ?: 120);
            return [
                'success' => true,
                'is_simulation' => true,
                'checkout_id' => $checkoutId,
                'checkout_url' => $checkoutUrl,
                'expiry_seconds' => $holdingSeconds,
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
                        'metadata' => [
                            'booking_reference' => $booking->booking_reference,
                            'expiry_seconds' => $holdingSeconds,
                        ],
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
                    'expiry_seconds' => 120,
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
                    'expiry_seconds' => 120,
                    'expires_at' => $booking->held_until?->toISOString(),
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
                'expiry_seconds' => 120,
                'expires_at' => $booking->held_until?->toISOString(),
            ];
        }
    }

    /**
     * Generate a Dynamic QR Code (QRPh / MPM) via PayMongo backend integration with expiry_seconds: 120
     */
    public function generateDynamicQr(Booking $booking, ?int $expirySeconds = null): array
    {
        $expirySeconds = $expirySeconds ?: (int) ($this->settings->holding_duration_seconds ?: 120);
        $secretKey = $this->settings->paymongo_secret_key ?: config('services.paymongo.secret_key');
        $isSimulation = $this->settings->paymongo_simulation_mode || empty($secretKey);
        $amountCentavos = (int) round(((float) $booking->total_amount) * 100);

        if ($isSimulation) {
            $fallbackQrUrl = route('paymongo.simulated.checkout', ['reference' => $booking->booking_reference]);
            return [
                'success' => true,
                'is_simulation' => true,
                'type' => 'dynamic',
                'reference' => $booking->booking_reference,
                'amount' => $amountCentavos,
                'currency' => 'PHP',
                'expiry_seconds' => $expirySeconds,
                'expires_at' => now()->addSeconds($expirySeconds)->toISOString(),
                'qr_code' => $fallbackQrUrl,
                'qr_image' => null,
            ];
        }

        try {
            // 1. Try Wallet Dynamic MPM QR Endpoint with expiry_seconds
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(8)->post('https://api.paymongo.com/v3/qr/mpm/generate', [
                'nation' => 'ph',
                'mode' => 'p2p',
                'type' => 'dynamic',
                'transaction_currency' => 'PHP',
                'transaction_amount' => $amountCentavos,
                'expiry_seconds' => $expirySeconds,
                'qr_image' => true,
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                return [
                    'success' => true,
                    'is_simulation' => false,
                    'type' => 'dynamic',
                    'reference' => $booking->booking_reference,
                    'amount' => $amountCentavos,
                    'currency' => 'PHP',
                    'expiry_seconds' => $expirySeconds,
                    'expires_at' => now()->addSeconds($expirySeconds)->toISOString(),
                    'qr_code' => $data['attributes']['qr_string'] ?? ($data['qr_string'] ?? null),
                    'qr_image' => $data['attributes']['qr_image'] ?? ($data['qr_image'] ?? null),
                ];
            }

            // 2. Alternative: Payment Intent + QRPh Payment Method with expiry_seconds
            $pmResponse = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(8)->post('https://api.paymongo.com/v1/payment_methods', [
                'data' => [
                    'attributes' => [
                        'type' => 'qrph',
                        'expiry_seconds' => $expirySeconds,
                    ]
                ]
            ]);

            if ($pmResponse->successful()) {
                $pmData = $pmResponse->json()['data'] ?? [];
                $paymentMethodId = $pmData['id'] ?? null;

                $piResponse = Http::withHeaders([
                    'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])->timeout(8)->post('https://api.paymongo.com/v1/payment_intents', [
                    'data' => [
                        'attributes' => [
                            'amount' => $amountCentavos,
                            'payment_method_allowed' => ['qrph'],
                            'currency' => 'PHP',
                            'description' => "Paddle Field Sports Center - {$booking->court->name} ({$booking->booking_reference})",
                            'metadata' => [
                                'booking_reference' => $booking->booking_reference,
                                'expiry_seconds' => $expirySeconds,
                            ]
                        ]
                    ]
                ]);

                if ($piResponse->successful()) {
                    $piData = $piResponse->json()['data'] ?? [];
                    $paymentIntentId = $piData['id'] ?? null;

                    $attachResponse = Http::withHeaders([
                        'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])->timeout(8)->post("https://api.paymongo.com/v1/payment_intents/{$paymentIntentId}/attach", [
                        'data' => [
                            'attributes' => [
                                'payment_method' => $paymentMethodId,
                                'return_url' => route('booking.track', ['reference' => $booking->booking_reference]),
                            ]
                        ]
                    ]);

                    if ($attachResponse->successful()) {
                        $attachData = $attachResponse->json()['data'] ?? [];
                        $nextAction = $attachData['attributes']['next_action'] ?? [];
                        $qrCodeUrl = $nextAction['code']['image_url'] ?? null;
                        $qrString = $nextAction['code']['string'] ?? null;

                        return [
                            'success' => true,
                            'is_simulation' => false,
                            'type' => 'dynamic',
                            'reference' => $booking->booking_reference,
                            'amount' => $amountCentavos,
                            'currency' => 'PHP',
                            'expiry_seconds' => $expirySeconds,
                            'expires_at' => now()->addSeconds($expirySeconds)->toISOString(),
                            'qr_code' => $qrString ?: $qrCodeUrl,
                            'qr_image' => $qrCodeUrl,
                            'payment_intent_id' => $paymentIntentId,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('PayMongo generateDynamicQr Exception: ' . $e->getMessage());
        }

        // Fallback simulation
        $fallbackQrUrl = route('paymongo.simulated.checkout', ['reference' => $booking->booking_reference]);
        return [
            'success' => true,
            'is_simulation' => true,
            'type' => 'dynamic',
            'reference' => $booking->booking_reference,
            'amount' => $amountCentavos,
            'currency' => 'PHP',
            'expiry_seconds' => $expirySeconds,
            'expires_at' => now()->addSeconds($expirySeconds)->toISOString(),
            'qr_code' => $fallbackQrUrl,
            'qr_image' => null,
        ];
    }

    /**
     * Expire an active PayMongo Checkout Session when the 2-minute hold window ends
     */
    public function expireCheckoutSession(?string $checkoutId): bool
    {
        if (empty($checkoutId) || str_starts_with($checkoutId, 'pm_sim_') || str_starts_with($checkoutId, 'pm_err_') || str_starts_with($checkoutId, 'pm_fallback_')) {
            return true;
        }

        $secretKey = $this->settings->paymongo_secret_key ?: config('services.paymongo.secret_key');
        if (empty($secretKey)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(8)->post("https://api.paymongo.com/v1/checkout_sessions/{$checkoutId}/expire");

            if ($response->successful()) {
                Log::info("PayMongo Checkout Session {$checkoutId} successfully expired via API after 2 minutes.");
                return true;
            } else {
                Log::warning("PayMongo API returned {$response->status()} while expiring session {$checkoutId}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Exception expiring PayMongo checkout session {$checkoutId}: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Verify PayMongo Webhook Signature
     */
    public function verifyWebhookSignature(?string $signatureHeader, string $payload): bool
    {
        $webhookSecret = $this->settings->paymongo_webhook_token ?: config('services.paymongo.webhook_token');
        if (empty($webhookSecret) || empty($signatureHeader)) {
            return true;
        }

        // PayMongo signature header format: t=timestamp,te=test_signature,li=live_signature
        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            $kv = explode('=', $part, 2);
            if (count($kv) === 2) {
                $parts[trim($kv[0])] = trim($kv[1]);
            }
        }

        $timestamp = $parts['t'] ?? null;
        $testSignature = $parts['te'] ?? null;
        $liveSignature = $parts['li'] ?? null;

        if (!$timestamp || (!$testSignature && !$liveSignature)) {
            return false;
        }

        $computedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $webhookSecret);

        if ($testSignature && hash_equals($computedSignature, $testSignature)) {
            return true;
        }

        if ($liveSignature && hash_equals($computedSignature, $liveSignature)) {
            return true;
        }

        return false;
    }

    /**
     * Check if a PayMongo Checkout Session has been paid
     */
    public function checkCheckoutSessionStatus(?string $checkoutId): array
    {
        if (empty($checkoutId) || str_starts_with($checkoutId, 'pm_sim_') || str_starts_with($checkoutId, 'pm_err_') || str_starts_with($checkoutId, 'pm_fallback_')) {
            return ['paid' => false, 'status' => 'simulation_or_missing'];
        }

        $secretKey = $this->settings->paymongo_secret_key ?: config('services.paymongo.secret_key');
        if (empty($secretKey)) {
            return ['paid' => false, 'status' => 'missing_secret_key'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($secretKey . ':'),
                'Accept' => 'application/json',
            ])->timeout(8)->get("https://api.paymongo.com/v1/checkout_sessions/{$checkoutId}");

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                $attributes = $data['attributes'] ?? [];

                $isPaid = !empty($attributes['paid_at']);

                $payments = $attributes['payments'] ?? [];
                foreach ($payments as $payment) {
                    if (($payment['attributes']['status'] ?? '') === 'paid') {
                        $isPaid = true;
                        break;
                    }
                }

                $intentStatus = $attributes['payment_intent']['attributes']['status'] ?? '';
                if ($intentStatus === 'succeeded') {
                    $isPaid = true;
                }

                return [
                    'paid' => $isPaid,
                    'status' => $attributes['status'] ?? 'active',
                    'paid_at' => $attributes['paid_at'] ?? null,
                    'payment_method_used' => $attributes['payment_method_used'] ?? null,
                    'reference_number' => $attributes['reference_number'] ?? ($attributes['metadata']['booking_reference'] ?? null),
                ];
            } else {
                Log::warning("PayMongo API returned {$response->status()} for session {$checkoutId}: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Exception checking PayMongo checkout session {$checkoutId}: " . $e->getMessage());
        }

        return ['paid' => false, 'status' => 'error'];
    }
}
