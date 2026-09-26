<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\FacilityPhoto;
use App\Models\VenueSetting;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Main Landing & Booking Page
     */
    public function index(Request $request)
    {
        $settings = VenueSetting::getSettings();
        $courts = Court::where('is_active', true)->orderBy('court_number')->get();
        $photos = FacilityPhoto::where('is_featured', true)->orderBy('sort_order')->get();

        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));

        // Ensure date is not in the past
        if (Carbon::parse($selectedDate)->lt(Carbon::today())) {
            $selectedDate = Carbon::today()->format('Y-m-d');
        }

        $availability = $this->bookingService->getAvailability($selectedDate);

        return view('welcome', compact('settings', 'courts', 'photos', 'selectedDate', 'availability'));
    }

    /**
     * Dedicated Booking Tracker Page
     */
    public function trackBooking(string $reference)
    {
        $settings = VenueSetting::getSettings();
        $booking = Booking::with(['court', 'slots', 'approver'])
            ->where('booking_reference', $reference)
            ->firstOrFail();

        // Check if hold is expired
        if ($booking->booking_status === 'held' && $booking->isExpired()) {
            $booking->update(['booking_status' => 'expired']);
            $booking->slots()->update(['status' => 'released']);
        }

        return view('bookings.track', compact('settings', 'booking'));
    }

    /**
     * Booking Lookup by reference or phone number
     */
    public function lookup(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:50',
        ]);

        $query = trim($request->input('query'));

        // Try exact reference match first
        $booking = Booking::where('booking_reference', $query)->first();

        if ($booking) {
            return redirect()->route('booking.track', ['reference' => $booking->booking_reference]);
        }

        // Try phone search
        $bookings = Booking::where('customer_phone', 'like', "%{$query}%")
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        if ($bookings->count() === 1) {
            return redirect()->route('booking.track', ['reference' => $bookings->first()->booking_reference]);
        }

        if ($bookings->count() > 1) {
            $settings = VenueSetting::getSettings();
            return view('bookings.lookup-results', compact('settings', 'bookings', 'query'));
        }

        return back()->with('error', "No reservation found for reference or phone '{$query}'. Please check and try again.");
    }
}
