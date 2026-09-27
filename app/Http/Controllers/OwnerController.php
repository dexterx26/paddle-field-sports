<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\FacilityPhoto;
use App\Models\VenueSetting;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OwnerController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Dashboard Overview
     */
    public function dashboard(Request $request)
    {
        $settings = VenueSetting::getSettings();
        $this->bookingService->releaseExpiredHolds();

        $today = Carbon::today()->format('Y-m-d');
        $courts = Court::where('is_active', true)->orderBy('court_number')->get();
        $selectedCourtId = $request->input('court_id');

        $pendingApprovals = Booking::with('court')
            ->where('booking_status', 'pending_approval')
            ->orderBy('created_at', 'asc')
            ->get();

        $nowTime = Carbon::now()->format('H:i');

        $todayBookings = Booking::with(['court', 'approver', 'slots'])
            ->whereDate('booking_date', $today)
            ->whereIn('booking_status', ['confirmed', 'pending_approval', 'held'])
            ->get()
            ->map(function ($b) use ($nowTime) {
                $endTime = substr($b->end_time, 0, 5);
                $startTime = substr($b->start_time, 0, 5);
                if ($endTime === '24:00' || ($endTime === '00:00' && $startTime > '12:00')) {
                    $isPast = false;
                } else {
                    $isPast = $endTime <= $nowTime;
                }
                $b->is_past = $isPast;
                return $b;
            })->sort(function ($a, $b) {
                // Active/upcoming at the top (0), past at the bottom (1)
                if ($a->is_past !== $b->is_past) {
                    return $a->is_past ? 1 : -1;
                }
                // Sort time descending
                $timeA = substr($a->start_time, 0, 5);
                $timeB = substr($b->start_time, 0, 5);
                $cmp = strcmp($timeB, $timeA);
                if ($cmp !== 0) {
                    return $cmp;
                }
                return ($a->court?->court_number ?? 0) <=> ($b->court?->court_number ?? 0);
            })->values();

        $totalRevenue = Booking::where('booking_status', 'confirmed')->sum('total_amount');
        $totalConfirmedCount = Booking::where('booking_status', 'confirmed')->count();
        $totalCourtsCount = $courts->count();

        $recentBookings = Booking::with(['court', 'approver', 'slots'])
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();

        return view('owner.dashboard', compact(
            'settings',
            'pendingApprovals',
            'todayBookings',
            'totalRevenue',
            'totalConfirmedCount',
            'totalCourtsCount',
            'recentBookings',
            'courts',
            'selectedCourtId'
        ));
    }

    /**
     * Pending Manual Receipt Approvals Queue
     */
    public function approvals()
    {
        $settings = VenueSetting::getSettings();
        $pendingBookings = Booking::with(['court', 'slots'])
            ->where('booking_status', 'pending_approval')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return view('owner.approvals', compact('settings', 'pendingBookings'));
    }

    /**
     * Approve a pending booking
     */
    public function approveBooking(int $id)
    {
        $booking = Booking::with('court')->findOrFail($id);

        if ($booking->booking_status !== 'pending_approval') {
            return back()->with('error', "Booking {$booking->booking_reference} is not pending approval.");
        }

        $this->bookingService->confirmBooking($booking, Auth::id(), 'manual_receipt');

        return back()->with('success', "Booking {$booking->booking_reference} for {$booking->customer_name} has been APPROVED and confirmed!");
    }

    /**
     * Reject a pending booking
     */
    public function rejectBooking(Request $request, int $id)
    {
        $booking = Booking::with('court')->findOrFail($id);

        if ($booking->booking_status !== 'pending_approval') {
            return back()->with('error', "Booking {$booking->booking_reference} is not pending approval.");
        }

        $reason = $request->input('reason', 'Payment receipt was invalid or could not be verified.');

        $this->bookingService->rejectBooking($booking, $reason, Auth::id());

        return back()->with('info', "Booking {$booking->booking_reference} was rejected and slots released.");
    }

    /**
     * Court Management
     */
    public function courts()
    {
        $settings = VenueSetting::getSettings();
        $courts = Court::withTrashed()->withCount(['bookings as total_bookings'])->orderBy('court_number')->get();

        return view('owner.courts.index', compact('settings', 'courts'));
    }

    /**
     * Store new court
     */
    public function storeCourt(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'court_number' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:indoor,outdoor',
            'surface_type' => 'required|string|max:100',
            'price_per_hour' => 'required|numeric|min:0',
            'max_players' => 'required|integer|min:1|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('courts', 'public');
        } else {
            // Default image based on type
            $imagePath = $data['type'] === 'outdoor' ? 'courts/court-2.jpg' : 'courts/court-1.jpg';
        }

        Court::create([
            'name' => $data['name'],
            'court_number' => $data['court_number'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'surface_type' => $data['surface_type'],
            'price_per_hour' => $data['price_per_hour'],
            'max_players' => $data['max_players'],
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('owner.courts.index')
            ->with('success', "{$data['name']} has been created successfully!");
    }

    /**
     * Update Court Pricing, Max Players, and Details
     * NOTE: Requirement 7: "if they edited the price, only the new booking will be affected"
     * Updating courts table here only affects new reservations since existing bookings
     * maintain their captured snapshot in bookings.rate_per_hour.
     */
    public function updateCourt(Request $request, int $id)
    {
        $court = Court::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'court_number' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:indoor,outdoor',
            'surface_type' => 'required|string|max:100',
            'price_per_hour' => 'required|numeric|min:0',
            'max_players' => 'required|integer|min:1|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'is_active' => 'nullable|boolean',
        ]);

        $updateData = [
            'name' => $data['name'],
            'court_number' => $data['court_number'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'surface_type' => $data['surface_type'],
            'price_per_hour' => $data['price_per_hour'], // New rate applied only for future bookings!
            'max_players' => $data['max_players'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('image')) {
            $updateData['image_path'] = $request->file('image')->store('courts', 'public');
        }

        $court->update($updateData);

        return redirect()->route('owner.courts.index')
            ->with('success', "{$court->name} updated! New price (₱" . number_format($data['price_per_hour'], 2) . "/hr) and player capacity ({$data['max_players']} players) applied to all future bookings.");
    }

    /**
     * Soft delete/archive court (hides from public schedule, preserves past bookings and earnings computation)
     */
    public function destroyCourt(int $id)
    {
        $court = Court::findOrFail($id);
        $court->update(['is_active' => false]);
        $court->delete(); // Soft delete: sets deleted_at, hides from schedule while keeping bookings & earnings computation intact

        return back()->with('success', "{$court->name} has been archived. It is now hidden from the public schedule, while past reservations and total earnings are safely preserved.");
    }

    /**
     * Restore soft-deleted court
     */
    public function restoreCourt(int $id)
    {
        $court = Court::withTrashed()->findOrFail($id);
        $court->restore();
        $court->update(['is_active' => true]);

        return back()->with('success', "{$court->name} has been restored and is active again on the schedule.");
    }

    /**
     * Facility Photos Management (Requirement 8)
     */
    public function photos()
    {
        $settings = VenueSetting::getSettings();
        $photos = FacilityPhoto::orderBy('sort_order')->orderBy('id', 'desc')->get();

        return view('owner.photos.index', compact('settings', 'photos'));
    }

    /**
     * Upload photo for website main page
     */
    public function storePhoto(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|string|in:court,lounge,amenity,event,general',
            'caption' => 'nullable|string|max:300',
            'sort_order' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:6144', // Max 6MB
        ]);

        $path = $request->file('photo')->store('gallery', 'public');

        FacilityPhoto::create([
            'title' => trim($request->input('title')),
            'category' => $request->input('category'),
            'image_path' => $path,
            'caption' => $request->input('caption'),
            'sort_order' => $request->input('sort_order', 0),
            'is_featured' => $request->boolean('is_featured', true),
        ]);

        return redirect()->route('owner.photos.index')
            ->with('success', 'Photo uploaded successfully! It is now live on the website main page.');
    }

    /**
     * Delete photo
     */
    public function destroyPhoto(int $id)
    {
        $photo = FacilityPhoto::findOrFail($id);

        if ($photo->image_path && Storage::disk('public')->exists($photo->image_path)) {
            Storage::disk('public')->delete($photo->image_path);
        }

        $photo->delete();

        return back()->with('success', 'Photo removed from website gallery.');
    }

    /**
     * Payment & Venue Settings
     */
    public function settings()
    {
        $settings = VenueSetting::getSettings();
        return view('owner.settings', compact('settings'));
    }

    /**
     * Update Payment & Venue Settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'venue_name' => 'required|string|max:150',
            'tagline' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'opening_time' => 'required|string',
            'closing_time' => 'required|string',
            'currency' => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:5',
            'payment_mode' => 'required|in:manual_receipt,xendit,paymongo',
            'holding_duration_seconds' => 'required|integer|min:30|max:600',
            // Xendit credentials
            'xendit_secret_key' => 'nullable|string',
            'xendit_public_key' => 'nullable|string',
            'xendit_webhook_token' => 'nullable|string',
            'xendit_simulation_mode' => 'nullable|boolean',
            // PayMongo credentials
            'paymongo_secret_key' => 'nullable|string',
            'paymongo_public_key' => 'nullable|string',
            'paymongo_webhook_token' => 'nullable|string',
            'paymongo_simulation_mode' => 'nullable|boolean',
            // Manual receipt info
            'manual_bank_name' => 'nullable|string|max:150',
            'manual_account_name' => 'nullable|string|max:150',
            'manual_account_number' => 'nullable|string|max:100',
            'manual_payment_instructions' => 'nullable|string|max:2000',
            'manual_payment_qr' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $settings = VenueSetting::getSettings();

        $data = $request->except(['_token', 'manual_payment_qr']);
        $data['xendit_simulation_mode'] = $request->boolean('xendit_simulation_mode', false);
        $data['paymongo_simulation_mode'] = $request->boolean('paymongo_simulation_mode', false);

        if ($request->hasFile('manual_payment_qr')) {
            $data['manual_payment_qr'] = $request->file('manual_payment_qr')->store('qr', 'public');
        }

        $settings->update($data);

        return redirect()->route('owner.settings')
            ->with('success', 'Settings updated successfully! Payment mode is currently set to: ' . strtoupper(str_replace('_', ' ', $settings->payment_mode)));
    }

    /**
     * All Bookings Ledger
     */
    public function allBookings(Request $request)
    {
        $settings = VenueSetting::getSettings();
        $courts = Court::orderBy('court_number')->get();

        $allowedStatuses = ['confirmed', 'pending_approval', 'held', 'rejected', 'expired'];

        // Default status is 'confirmed' unless explicitly provided
        if ($request->has('status')) {
            $rawStatus = $request->input('status');
            $status = in_array($rawStatus, $allowedStatuses) ? $rawStatus : 'all';
        } else {
            $status = 'confirmed';
        }

        $query = Booking::with(['court', 'approver'])->orderBy('booking_date', 'desc')->orderBy('start_time', 'desc');

        if ($request->filled('court_id')) {
            $query->where('court_id', $request->input('court_id'));
        }

        // Apply status filter if not 'all'
        if ($status !== 'all') {
            $query->where('booking_status', $status);
        }

        if ($request->filled('date')) {
            $query->whereDate('booking_date', $request->input('date'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $bookings = $query->paginate(20)->withQueryString();

        $statusCounts = [
            'all' => Booking::count(),
            'confirmed' => Booking::where('booking_status', 'confirmed')->count(),
            'pending_approval' => Booking::where('booking_status', 'pending_approval')->count(),
            'held' => Booking::where('booking_status', 'held')->count(),
            'rejected' => Booking::where('booking_status', 'rejected')->count(),
            'expired' => Booking::where('booking_status', 'expired')->count(),
        ];

        return view('owner.bookings.index', compact('settings', 'courts', 'bookings', 'status', 'statusCounts'));
    }
}
