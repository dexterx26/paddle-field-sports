<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\VenueSetting;
use App\Services\BookingService;
use App\Services\PayMongoService;
use App\Services\XenditService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BookingApiController extends Controller
{
    protected BookingService $bookingService;
    protected XenditService $xenditService;
    protected PayMongoService $paymongoService;

    public function __construct(
        BookingService $bookingService,
        XenditService $xenditService,
        PayMongoService $paymongoService
    ) {
        $this->bookingService = $bookingService;
        $this->xenditService = $xenditService;
        $this->paymongoService = $paymongoService;
    }

    /**
     * Get live availability for a specific date
     */
    public function availability(Request $request): JsonResponse
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        if (Carbon::parse($date)->lt(Carbon::today())) {
            $date = Carbon::today()->format('Y-m-d');
        }

        $availability = $this->bookingService->getAvailability($date);

        return response()->json([
            'success' => true,
            'date' => $date,
            'courts' => $availability,
            'server_time' => now()->toISOString(),
        ]);
    }

    /**
     * Step 1 (Online Gateway Flow): Hold slots for 2 minutes and initiate Xendit or PayMongo checkout
     */
    public function hold(Request $request): JsonResponse
    {
        $request->validate([
            'court_id' => 'required|exists:courts,id',
            'date' => 'required|date|after_or_equal:today',
            'slots' => 'required|array|min:1',
            'slots.*' => 'string|date_format:H:i',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:25',
            'customer_email' => 'nullable|email|max:100',
            'players_count' => 'nullable|integer|min:1|max:20',
            'notes' => 'nullable|string|max:500',
        ]);

        $courtId = (int) $request->input('court_id');
        $date = $request->input('date');
        $slots = $request->input('slots');

        $customerData = [
            'name' => trim($request->input('customer_name')),
            'phone' => trim($request->input('customer_phone')),
            'email' => $request->input('customer_email'),
            'players_count' => $request->input('players_count'),
            'notes' => $request->input('notes'),
        ];

        $userId = Auth::id();
        $settings = VenueSetting::getSettings();
        $isPayMongo = $settings->payment_mode === 'paymongo';

        try {
            $holdResult = $this->bookingService->holdSlots($courtId, $date, $slots, $customerData, $userId);
            $booking = $holdResult['booking'];

            if ($isPayMongo) {
                // Generate PayMongo Checkout Session (or simulation)
                $payResult = $this->paymongoService->createCheckoutSession($booking);
                $invoiceUrl = $payResult['checkout_url'] ?? null;
                $isSimulation = $payResult['is_simulation'] ?? true;
                $gatewayName = 'PayMongo';
            } else {
                // Generate Xendit invoice (or simulation)
                $invoiceResult = $this->xenditService->createInvoice($booking);
                $invoiceUrl = $invoiceResult['invoice_url'] ?? null;
                $isSimulation = $invoiceResult['is_simulation'] ?? true;
                $gatewayName = 'Xendit';
            }

            $durationMinutes = max(1, (int) round(($holdResult['remaining_seconds'] ?? 120) / 60));

            return response()->json([
                'success' => true,
                'message' => "Timeslot held successfully for {$durationMinutes} minutes. Please complete payment with {$gatewayName} before time runs out.",
                'reference' => $booking->booking_reference,
                'held_until' => $holdResult['held_until'],
                'remaining_seconds' => $holdResult['remaining_seconds'],
                'expiry_seconds' => $isPayMongo ? ($payResult['expiry_seconds'] ?? 120) : null,
                'total_amount' => $holdResult['total_amount'],
                'formatted_amount' => $holdResult['formatted_amount'],
                'invoice_url' => $invoiceUrl,
                'gateway' => $isPayMongo ? 'paymongo' : 'xendit',
                'gateway_name' => $gatewayName,
                'is_simulation' => $isSimulation,
                'track_url' => route('booking.track', ['reference' => $booking->booking_reference]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Step 2 (Manual Receipt Flow): Submit booking with uploaded receipt image
     */
    public function submitManualReceipt(Request $request)
    {
        $request->validate([
            'court_id' => 'required|exists:courts,id',
            'date' => 'required|date|after_or_equal:today',
            'slots' => 'required|string', // JSON array string or array
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:25',
            'customer_email' => 'nullable|email|max:100',
            'players_count' => 'nullable|integer|min:1|max:20',
            'notes' => 'nullable|string|max:500',
            'receipt' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB
        ]);

        $slotsRaw = $request->input('slots');
        $slots = is_array($slotsRaw) ? $slotsRaw : json_decode($slotsRaw, true);

        if (!is_array($slots) || empty($slots)) {
            return back()->with('error', 'Please select at least one timeslot.')->withInput();
        }

        $courtId = (int) $request->input('court_id');
        $date = $request->input('date');

        $receiptPath = $request->file('receipt')->store('receipts', 'public');

        $customerData = [
            'name' => trim($request->input('customer_name')),
            'phone' => trim($request->input('customer_phone')),
            'email' => $request->input('customer_email'),
            'players_count' => $request->input('players_count'),
            'notes' => $request->input('notes'),
        ];

        $userId = Auth::id();

        try {
            $booking = $this->bookingService->createManualReceiptBooking(
                $courtId,
                $date,
                $slots,
                $customerData,
                $receiptPath,
                $userId
            );

            return redirect()->route('booking.track', ['reference' => $booking->booking_reference])
                ->with('success', 'Your reservation request and payment receipt have been submitted! The court owner will verify your receipt shortly.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Check real-time status of a booking
     */
    public function status(string $reference): JsonResponse
    {
        $booking = Booking::with('court')
            ->where('booking_reference', $reference)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking reference not found',
            ], 404);
        }

        // Direct PayMongo check: if not confirmed, query PayMongo API to confirm payment
        if ($booking->booking_status !== 'confirmed' && !empty($booking->paymongo_checkout_id)) {
            $check = $this->paymongoService->checkCheckoutSessionStatus($booking->paymongo_checkout_id);
            if (!empty($check['paid'])) {
                $booking = $this->bookingService->confirmBooking($booking, null, 'paymongo');
            }
        }

        $this->bookingService->releaseExpiredHolds();
        $booking->refresh();

        $isHeld = $booking->isHeld();
        $isExpired = $booking->isExpired();

        if ($isExpired && $booking->booking_status === 'held') {
            $booking->update(['booking_status' => 'expired']);
            $booking->slots()->update(['status' => 'released']);
        }

        return response()->json([
            'success' => true,
            'reference' => $booking->booking_reference,
            'booking_status' => $booking->booking_status,
            'payment_status' => $booking->payment_status,
            'is_held' => $isHeld,
            'is_expired' => $isExpired,
            'remaining_seconds' => $booking->remaining_hold_seconds,
            'held_until' => $booking->held_until?->toISOString(),
            'court_name' => $booking->court->name,
            'date' => $booking->booking_date->format('M d, Y'),
            'time' => $booking->start_time . ' - ' . $booking->end_time,
            'total_amount' => $booking->formatted_amount,
        ]);
    }

    /**
     * Cancel hold manually (e.g. user aborts checkout)
     */
    public function cancelHold(string $reference): JsonResponse
    {
        $cancelled = $this->bookingService->cancelHeldBooking($reference);

        return response()->json([
            'success' => $cancelled,
            'message' => $cancelled ? 'Reservation hold released.' : 'Hold could not be cancelled or already expired.',
        ]);
    }

    /**
     * Simulated Xendit Checkout Page
     */
    public function simulatedCheckout(string $reference)
    {
        $booking = Booking::with('court')
            ->where('booking_reference', $reference)
            ->firstOrFail();

        $settings = VenueSetting::getSettings();

        if ($booking->booking_status === 'confirmed') {
            return redirect()->route('booking.track', ['reference' => $booking->booking_reference]);
        }

        if ($booking->isExpired()) {
            return redirect()->route('booking.track', ['reference' => $booking->booking_reference])
                ->with('error', 'Your 2-minute reservation hold has expired. Please select your slot again.');
        }

        return view('payments.xendit-simulation', compact('booking', 'settings'));
    }

    /**
     * Simulate Payment Confirmation (for testing or demo)
     */
    public function simulatePayment(Request $request, string $reference)
    {
        $booking = Booking::with(['court', 'slots'])
            ->where('booking_reference', $reference)
            ->firstOrFail();

        if ($booking->isExpired()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hold has expired. Slots were released.',
                ], 422);
            }
            return redirect()->route('booking.track', ['reference' => $booking->booking_reference])
                ->with('error', 'Hold time expired before payment was processed.');
        }

        $method = $request->input('payment_channel', 'GCash');
        $isPayMongo = str_contains(strtolower($method), 'paymongo') || $booking->payment_method === 'paymongo';
        $gateway = $isPayMongo ? 'paymongo' : 'xendit';

        $this->bookingService->confirmBooking($booking, null, $gateway);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Payment simulated successfully! Booking is now confirmed via {$gateway}.",
                'track_url' => route('booking.track', ['reference' => $booking->booking_reference]),
            ]);
        }

        return redirect()->route('booking.track', ['reference' => $booking->booking_reference])
            ->with('success', 'Payment verified! Your court reservation is officially confirmed.');
    }

    /**
     * Simulated PayMongo Checkout Page
     */
    public function paymongoCheckout(string $reference)
    {
        $booking = Booking::with('court')
            ->where('booking_reference', $reference)
            ->firstOrFail();

        $settings = VenueSetting::getSettings();

        if ($booking->booking_status === 'confirmed') {
            return redirect()->route('booking.track', ['reference' => $booking->booking_reference]);
        }

        if ($booking->isExpired()) {
            return redirect()->route('booking.track', ['reference' => $booking->booking_reference])
                ->with('error', 'Your reservation hold has expired. Please select your slot again.');
        }

        // If this booking has an external live PayMongo checkout URL, redirect directly to PayMongo
        if (!empty($booking->paymongo_payment_url) && str_starts_with($booking->paymongo_payment_url, 'http') && !str_contains($booking->paymongo_payment_url, 'paymongo-checkout')) {
            return redirect()->away($booking->paymongo_payment_url);
        }

        return view('payments.paymongo-simulation', compact('booking', 'settings'));
    }

    /**
     * Generate dynamic QR via PayMongo backend integration with expiry_seconds: 120
     */
    public function generatePayMongoQr(string $reference): JsonResponse
    {
        $booking = Booking::with('court')
            ->where('booking_reference', $reference)
            ->firstOrFail();

        $qrData = $this->paymongoService->generateDynamicQr($booking, 120);

        return response()->json($qrData);
    }

    /**
     * Official PayMongo Webhook Handler
     */
    public function paymongoWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('paymongo-signature');

        if (!$this->paymongoService->verifyWebhookSignature($signature, $request->getContent())) {
            return response()->json(['message' => 'Unauthorized signature'], 401);
        }

        Log::info('PayMongo Webhook received: ', $payload);

        $eventType = $payload['data']['attributes']['type'] ?? null;
        $eventData = $payload['data']['attributes']['data'] ?? [];

        // Check if event is checkout_session.payment.paid or payment.paid
        if (str_contains($eventType, 'paid')) {
            $ref = $eventData['attributes']['reference_number']
                ?? ($eventData['attributes']['metadata']['booking_reference'] ?? null);

            $booking = null;
            if ($ref) {
                $booking = Booking::where('booking_reference', $ref)->first();
            }

            // Also search by PayMongo Checkout Session ID if reference number not found
            if (!$booking && !empty($eventData['id'])) {
                $booking = Booking::where('paymongo_checkout_id', $eventData['id'])->first();
            }

            if ($booking && $booking->booking_status !== 'confirmed') {
                $this->bookingService->confirmBooking($booking, null, 'paymongo');
                Log::info("PayMongo Webhook confirmed booking {$booking->booking_reference}");
                return response()->json(['message' => 'Booking confirmed via PayMongo']);
            }
        }

        return response()->json(['message' => 'PayMongo webhook processed']);
    }

    /**
     * Official Xendit Webhook Handler
     */
    public function xenditWebhook(Request $request): JsonResponse
    {
        $callbackToken = $request->header('x-callback-token');

        if (!$this->xenditService->verifyWebhookToken($callbackToken)) {
            Log::warning('Xendit webhook rejected: Invalid callback token');
            return response()->json(['message' => 'Unauthorized token'], 401);
        }

        $payload = $request->all();
        $externalId = $payload['external_id'] ?? null;
        $status = $payload['status'] ?? null;

        Log::info('Xendit Webhook received: ', ['external_id' => $externalId, 'status' => $status]);

        if (!$externalId) {
            return response()->json(['message' => 'No external_id provided'], 400);
        }

        $booking = Booking::where('booking_reference', $externalId)->first();

        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        if ($status === 'PAID' || $status === 'SETTLED') {
            $this->bookingService->confirmBooking($booking, null, 'xendit');
            return response()->json(['message' => 'Booking confirmed via Xendit']);
        }

        if ($status === 'EXPIRED') {
            $booking->update(['booking_status' => 'expired']);
            $booking->slots()->update(['status' => 'released']);
            return response()->json(['message' => 'Booking marked expired']);
        }

        return response()->json(['message' => 'Webhook received and processed']);
    }
}
