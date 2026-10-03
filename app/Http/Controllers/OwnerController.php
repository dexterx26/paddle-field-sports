<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Court;
use App\Models\FacilityPhoto;
use App\Models\User;
use App\Models\VenueSetting;
use App\Services\BookingService;
use App\Services\EmailNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!$file->isValid()) {
                $errorCode = $file->getError();
                $errorMsg = match ($errorCode) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded court photo exceeds the server upload limit. Please select an image under 5MB or compress it.',
                    UPLOAD_ERR_PARTIAL => 'The photo was only partially uploaded. Please try again.',
                    UPLOAD_ERR_NO_FILE => 'No photo file was received.',
                    default => 'Court image upload failed (Error code: ' . $errorCode . '). Please try again.',
                };
                return back()->withInput()->with('error', $errorMsg);
            }
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'court_number' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:indoor,outdoor',
            'surface_type' => 'required|string|max:100',
            'opening_time' => 'nullable|string|regex:/^\d{2}:\d{2}$/',
            'closing_time' => 'nullable|string|regex:/^\d{2}:\d{2}$/',
            'price_per_hour' => 'required|numeric|min:0',
            'max_players' => 'required|integer|min:1|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,avif,svg|max:10240',
            'image_url' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('courts', 'public');
            try {
                $publicTargetDir = public_path('storage/courts');
                if (!file_exists($publicTargetDir)) {
                    @mkdir($publicTargetDir, 0755, true);
                }
                @copy(storage_path('app/public/' . $imagePath), public_path('storage/' . $imagePath));
            } catch (\Throwable $e) {}
        } elseif ($request->filled('image_url')) {
            $imagePath = trim($request->input('image_url'));
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
            'opening_time' => $data['opening_time'] ?? '06:00',
            'closing_time' => $data['closing_time'] ?? '00:00',
            'price_per_hour' => $data['price_per_hour'],
            'max_players' => $data['max_players'],
            'image_path' => $imagePath,
            'is_active' => $request->has('is_active'),
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

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if (!$file->isValid()) {
                $errorCode = $file->getError();
                $errorMsg = match ($errorCode) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded court photo exceeds the server upload limit. Please select an image under 5MB or compress it.',
                    UPLOAD_ERR_PARTIAL => 'The photo was only partially uploaded. Please try again.',
                    UPLOAD_ERR_NO_FILE => 'No photo file was received.',
                    default => 'Court image upload failed (Error code: ' . $errorCode . '). Please try again.',
                };
                return back()->withInput()->with('error', $errorMsg);
            }
        }

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'court_number' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:indoor,outdoor',
            'surface_type' => 'required|string|max:100',
            'opening_time' => 'nullable|string|regex:/^\d{2}:\d{2}$/',
            'closing_time' => 'nullable|string|regex:/^\d{2}:\d{2}$/',
            'price_per_hour' => 'required|numeric|min:0',
            'max_players' => 'required|integer|min:1|max:20',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,avif,svg|max:10240',
            'image_url' => 'nullable|url|max:500',
            'is_active' => 'nullable|boolean',
        ]);

        $updateData = [
            'name' => $data['name'],
            'court_number' => $data['court_number'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'surface_type' => $data['surface_type'],
            'opening_time' => $data['opening_time'] ?? '06:00',
            'closing_time' => $data['closing_time'] ?? '00:00',
            'price_per_hour' => $data['price_per_hour'], // New rate applied only for future bookings!
            'max_players' => $data['max_players'],
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('image')) {
            // Delete old court image from storage if it was a local file
            if ($court->image_path && !str_starts_with($court->image_path, 'http')) {
                if (Storage::disk('public')->exists($court->image_path)) {
                    Storage::disk('public')->delete($court->image_path);
                }
                if (file_exists(public_path('storage/' . $court->image_path))) {
                    @unlink(public_path('storage/' . $court->image_path));
                }
            }

            $path = $request->file('image')->store('courts', 'public');
            $updateData['image_path'] = $path;

            // Dual storage: Also copy to public/storage if directory exists
            try {
                $publicTargetDir = public_path('storage/courts');
                if (!file_exists($publicTargetDir)) {
                    @mkdir($publicTargetDir, 0755, true);
                }
                @copy(storage_path('app/public/' . $path), public_path('storage/' . $path));
            } catch (\Throwable $e) {}
        } elseif ($request->filled('image_url')) {
            $updateData['image_path'] = trim($request->input('image_url'));
        }

        $court->update($updateData);

        return redirect()->route('owner.courts.index')
            ->with('success', "{$court->name} updated successfully! New price (₱" . number_format($data['price_per_hour'], 2) . "/hr) and player capacity ({$data['max_players']} players) applied to all future bookings.");
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
        // 1. Check for PHP upload-level errors (e.g. upload_max_filesize exceeded)
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if (!$file->isValid()) {
                $errorCode = $file->getError();
                $errorMsg = match ($errorCode) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded photo exceeds the server upload limit. Please select an image under 5MB or compress it.',
                    UPLOAD_ERR_PARTIAL => 'The photo was only partially uploaded. Please try again.',
                    UPLOAD_ERR_NO_FILE => 'No photo file was received. Please select an image to upload.',
                    default => 'Photo upload failed (Error code: ' . $errorCode . '). Please try again.',
                };
                return back()->withInput()->with('error', $errorMsg);
            }
        }

        // 2. Validate form input
        $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|string|in:court,lounge,amenity,event,general',
            'caption' => 'nullable|string|max:300',
            'sort_order' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,avif,svg|max:10240', // Max 10MB
            'image_url' => 'nullable|url|max:500',
        ]);

        $imagePath = null;

        // If a file was uploaded
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('gallery', 'public');
            $imagePath = $path;

            // Dual storage: Also copy to public/storage if directory exists or can be created
            try {
                $publicTargetDir = public_path('storage/gallery');
                if (!file_exists($publicTargetDir)) {
                    @mkdir($publicTargetDir, 0755, true);
                }
                @copy(storage_path('app/public/' . $path), public_path('storage/' . $path));
            } catch (\Throwable $e) {
                // Ignore copy errors, fallback route will serve from storage_path
            }
        } elseif ($request->filled('image_url')) {
            $imagePath = trim($request->input('image_url'));
        } else {
            return back()->withInput()->with('error', 'Please select an image file to upload or enter an image URL.');
        }

        $sortOrder = (int) ($request->input('sort_order') ?: 0);
        $isFeatured = $request->has('is_featured');

        FacilityPhoto::create([
            'title' => trim($request->input('title')),
            'category' => $request->input('category'),
            'image_path' => $imagePath,
            'caption' => $request->input('caption'),
            'sort_order' => $sortOrder,
            'is_featured' => $isFeatured,
        ]);

        return redirect()->route('owner.photos.index')
            ->with('success', 'Photo uploaded successfully! It is now live on the website main page.');
    }

    /**
     * Update photo details
     */
    public function updatePhoto(Request $request, int $id)
    {
        $photo = FacilityPhoto::findOrFail($id);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            if (!$file->isValid()) {
                $errorCode = $file->getError();
                $errorMsg = match ($errorCode) {
                    UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded photo exceeds the server upload limit. Please select an image under 5MB or compress it.',
                    UPLOAD_ERR_PARTIAL => 'The photo was only partially uploaded. Please try again.',
                    UPLOAD_ERR_NO_FILE => 'No photo file was received. Please select an image to upload.',
                    default => 'Photo upload failed (Error code: ' . $errorCode . '). Please try again.',
                };
                return back()->withInput()->with('error', $errorMsg);
            }
        }

        $request->validate([
            'title' => 'required|string|max:150',
            'category' => 'required|string|in:court,lounge,amenity,event,general',
            'caption' => 'nullable|string|max:300',
            'sort_order' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif,avif,svg|max:10240',
            'image_url' => 'nullable|url|max:500',
        ]);

        $imagePath = $photo->image_path;

        if ($request->hasFile('photo')) {
            // Delete old file if present
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            if ($imagePath && file_exists(public_path('storage/' . $imagePath))) {
                @unlink(public_path('storage/' . $imagePath));
            }

            $path = $request->file('photo')->store('gallery', 'public');
            $imagePath = $path;

            try {
                $publicTargetDir = public_path('storage/gallery');
                if (!file_exists($publicTargetDir)) {
                    @mkdir($publicTargetDir, 0755, true);
                }
                @copy(storage_path('app/public/' . $path), public_path('storage/' . $path));
            } catch (\Throwable $e) {}
        } elseif ($request->filled('image_url')) {
            $imagePath = trim($request->input('image_url'));
        }

        $photo->update([
            'title' => trim($request->input('title')),
            'category' => $request->input('category'),
            'image_path' => $imagePath,
            'caption' => $request->input('caption'),
            'sort_order' => (int) ($request->input('sort_order') ?: 0),
            'is_featured' => $request->has('is_featured'),
        ]);

        return redirect()->route('owner.photos.index')
            ->with('success', 'Photo details updated successfully!');
    }

    /**
     * Delete photo
     */
    public function destroyPhoto(int $id)
    {
        $photo = FacilityPhoto::findOrFail($id);

        if ($photo->image_path) {
            if (Storage::disk('public')->exists($photo->image_path)) {
                Storage::disk('public')->delete($photo->image_path);
            }
            if (file_exists(public_path('storage/' . $photo->image_path))) {
                @unlink(public_path('storage/' . $photo->image_path));
            }
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

        $allowedStatuses = ['confirmed', 'pending_approval', 'held', 'cancelled', 'rejected', 'expired'];

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
            'cancelled' => Booking::where('booking_status', 'cancelled')->count(),
            'rejected' => Booking::where('booking_status', 'rejected')->count(),
            'expired' => Booking::where('booking_status', 'expired')->count(),
        ];

        return view('owner.bookings.index', compact('settings', 'courts', 'bookings', 'status', 'statusCounts'));
    }

    /**
     * Reserve / Block Court manually by Court Owner / Admin (offline / whole-court rental)
     */
    public function manualReserve(Request $request)
    {
        $data = $request->validate([
            'court_id' => 'required|exists:courts,id',
            'date' => 'required|date',
            'slot_mode' => 'required|in:all_day,custom',
            'slots' => 'nullable|array',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:25',
            'customer_email' => 'nullable|email|max:100',
            'players_count' => 'nullable|integer|min:1|max:20',
            'total_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid',
            'notes' => 'nullable|string|max:500',
        ]);

        $court = Court::findOrFail($data['court_id']);

        if ($data['slot_mode'] === 'all_day') {
            $slots = [];
            $startHour = $court->start_hour;
            $endHour = $court->end_hour;
            for ($h = $startHour; $h < $endHour; $h++) {
                $slots[] = sprintf('%02d:00', $h);
            }
            $data['slots'] = $slots;
        } else {
            if (empty($data['slots'])) {
                return back()->withInput()->with('error', 'Please select at least one timeslot when choosing Specific Time Slots.');
            }
        }

        try {
            $booking = $this->bookingService->manualReserveCourt($data, Auth::id());

            return back()->with('success', "Court {$court->name} reserved successfully for {$booking->customer_name} ({$booking->booking_reference}) on {$booking->booking_date->format('M d, Y')} from " . date('g:i A', strtotime($booking->start_time)) . " to " . date('g:i A', strtotime($booking->end_time)) . "!");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel an existing reservation of any user
     */
    public function cancelBooking(Request $request, int $id)
    {
        $booking = Booking::with('court')->findOrFail($id);

        if ($booking->booking_status === 'cancelled') {
            return back()->with('error', "Booking {$booking->booking_reference} is already cancelled.");
        }

        $reason = $request->input('reason', 'Cancelled by Court Owner.');

        $this->bookingService->cancelReservation($booking, $reason, Auth::id());

        return back()->with('success', "Reservation {$booking->booking_reference} for {$booking->customer_name} on {$booking->court->name} has been CANCELLED and timeslots released.");
    }

    /**
     * Admin Assistants Management (Added by Court Owner)
     */
    public function assistantsIndex()
    {
        if (!Auth::user()->canManageStaff()) {
            abort(403, 'Only court owners and administrators can manage admin assistants.');
        }

        $settings = VenueSetting::getSettings();
        $currentUser = Auth::user();

        $query = User::where('role', 'admin_assistant')->with('courtOwner');

        // If Court Owner, show assistants created by this owner or unassigned
        if ($currentUser->isOwner()) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('court_owner_id', $currentUser->id)
                  ->orWhereNull('court_owner_id');
            });
        }

        $assistants = $query->orderBy('id', 'desc')->get();
        $availableModules = User::availableModules();
        $defaultPermissions = User::defaultAssistantPermissions();

        return view('owner.assistants.index', compact('settings', 'assistants', 'availableModules', 'defaultPermissions'));
    }

    /**
     * Store new Admin Assistant
     */
    public function storeAssistant(Request $request)
    {
        if (!Auth::user()->canManageStaff()) {
            abort(403, 'Only court owners and administrators can manage admin assistants.');
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:6|confirmed',
            'modules' => 'nullable|array',
            'modules.*' => 'string|in:schedule,approvals,courts,photos,settings,users',
            'is_active' => 'nullable|boolean',
        ]);

        $modules = $request->input('modules');
        // If not supplied, apply default (schedule and approvals)
        if ($modules === null) {
            $modules = User::defaultAssistantPermissions();
        }

        $ownerId = Auth::user()->isOwner() ? Auth::id() : null;

        $assistant = User::create([
            'name' => trim($request->input('name')),
            'email' => strtolower(trim($request->input('email'))),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'role' => 'admin_assistant',
            'court_owner_id' => $ownerId,
            'permissions' => array_values($modules),
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Send registration confirmation email
        EmailNotificationService::sendRegistrationConfirmation($assistant);

        return redirect()->route('owner.assistants.index')
            ->with('success', "Admin Assistant '{$assistant->name}' created successfully with configured module access!");
    }

    /**
     * Update Admin Assistant details and module access
     */
    public function updateAssistant(Request $request, int $id)
    {
        if (!Auth::user()->canManageStaff()) {
            abort(403, 'Only court owners and administrators can manage admin assistants.');
        }

        $assistant = User::where('role', 'admin_assistant')->findOrFail($id);

        if (Auth::user()->isOwner() && $assistant->court_owner_id && $assistant->court_owner_id !== Auth::id()) {
            abort(403, 'You do not have permission to manage this admin assistant.');
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $assistant->id,
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
            'modules' => 'nullable|array',
            'modules.*' => 'string|in:schedule,approvals,courts,photos,settings,users',
            'is_active' => 'nullable|boolean',
        ]);

        $modules = $request->input('modules', []);

        $updateData = [
            'name' => trim($request->input('name')),
            'email' => strtolower(trim($request->input('email'))),
            'phone' => $request->input('phone'),
            'permissions' => array_values($modules),
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->input('password'));
        }

        $assistant->update($updateData);

        return redirect()->route('owner.assistants.index')
            ->with('success', "Admin Assistant '{$assistant->name}' updated successfully!");
    }

    /**
     * Toggle Assistant Active/Inactive status
     */
    public function toggleAssistantStatus(int $id)
    {
        if (!Auth::user()->canManageStaff()) {
            abort(403, 'Only court owners and administrators can manage admin assistants.');
        }

        $assistant = User::where('role', 'admin_assistant')->findOrFail($id);

        if (Auth::user()->isOwner() && $assistant->court_owner_id && $assistant->court_owner_id !== Auth::id()) {
            abort(403, 'You do not have permission to manage this admin assistant.');
        }

        $assistant->update([
            'is_active' => !$assistant->is_active,
        ]);

        $statusText = $assistant->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Admin Assistant '{$assistant->name}' has been {$statusText}.");
    }

    /**
     * Delete an Admin Assistant
     */
    public function destroyAssistant(int $id)
    {
        if (!Auth::user()->canManageStaff()) {
            abort(403, 'Only court owners and administrators can manage admin assistants.');
        }

        $assistant = User::where('role', 'admin_assistant')->findOrFail($id);

        if (Auth::user()->isOwner() && $assistant->court_owner_id && $assistant->court_owner_id !== Auth::id()) {
            abort(403, 'You do not have permission to manage this admin assistant.');
        }

        $name = $assistant->name;
        $assistant->delete();

        return back()->with('success', "Admin Assistant '{$name}' has been removed.");
    }

    /**
     * User Management Index (Accessible to Court Owner, System Admin, and Admin Assistant with 'users' module access)
     */
    public function usersIndex(Request $request)
    {
        $settings = VenueSetting::getSettings();
        $currentUser = Auth::user();

        $allowedRoles = ['admin', 'court_owner', 'admin_assistant', 'client'];
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');
        $search = $request->input('search');

        $sort = $request->input('sort');

        $query = User::with(['courtOwner'])
            ->withCount('bookings')
            ->withSum([
                'bookings as held_timeslots_count' => function ($q) {
                    $q->whereIn('booking_status', ['held', 'expired', 'cancelled']);
                }
            ], 'total_hours')
            ->withSum([
                'bookings as active_held_slots_count' => function ($q) {
                    $q->where('booking_status', 'held')->where('held_until', '>', now());
                }
            ], 'total_hours');

        // Role filtering
        if ($roleFilter && in_array($roleFilter, $allowedRoles)) {
            $query->where('role', $roleFilter);
        }

        // Status & Held filtering
        if ($statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('is_active', false);
        } elseif ($statusFilter === 'has_held') {
            $query->whereHas('bookings', function ($q) {
                $q->whereIn('booking_status', ['held', 'expired', 'cancelled']);
            });
        }

        if ($request->boolean('held_only')) {
            $query->whereHas('bookings', function ($q) {
                $q->whereIn('booking_status', ['held', 'expired', 'cancelled']);
            });
        }

        // Search filtering
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Ordering: if requested sort by held timeslots desc, otherwise role hierarchy
        if ($sort === 'held_desc') {
            $query->orderByDesc('held_timeslots_count')->orderBy('id', 'desc');
        } else {
            // Admins & Owners first, then Assistants, then Clients, ordered by newest
            $query->orderByRaw("CASE 
                WHEN role = 'admin' THEN 1 
                WHEN role = 'court_owner' THEN 2 
                WHEN role = 'admin_assistant' THEN 3 
                ELSE 4 END")
                ->orderBy('id', 'desc');
        }

        $users = $query->paginate(15)->withQueryString();

        // Counts for tabs & summary
        $counts = [
            'all' => User::count(),
            'client' => User::where('role', 'client')->count(),
            'admin_assistant' => User::where('role', 'admin_assistant')->count(),
            'court_owner' => User::where('role', 'court_owner')->count(),
            'admin' => User::where('role', 'admin')->count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'has_held' => User::whereHas('bookings', function ($q) {
                $q->whereIn('booking_status', ['held', 'expired', 'cancelled']);
            })->count(),
            'total_held_slots' => (int) Booking::whereNotNull('user_id')
                ->whereIn('booking_status', ['held', 'expired', 'cancelled'])
                ->sum('total_hours'),
        ];

        $availableModules = User::availableModules();
        $defaultPermissions = User::defaultAssistantPermissions();
        $courtOwners = User::where('role', 'court_owner')->orderBy('name')->get();

        return view('owner.users.index', compact(
            'settings',
            'users',
            'counts',
            'roleFilter',
            'statusFilter',
            'search',
            'sort',
            'availableModules',
            'defaultPermissions',
            'courtOwners'
        ));
    }

    /**
     * Store new User from User Management
     */
    public function storeUser(Request $request)
    {
        $currentUser = Auth::user();

        // Validate allowed roles based on actor's permission level
        $allowedRoles = ['client', 'admin_assistant', 'court_owner', 'admin'];
        if ($currentUser->isAdminAssistant()) {
            $allowedRoles = ['client', 'admin_assistant'];
        } elseif ($currentUser->isOwner()) {
            $allowedRoles = ['client', 'admin_assistant', 'court_owner'];
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:' . implode(',', $allowedRoles),
            'court_owner_id' => 'nullable|exists:users,id',
            'modules' => 'nullable|array',
            'modules.*' => 'string|in:schedule,approvals,courts,photos,settings,users',
            'is_active' => 'nullable|boolean',
        ]);

        $role = $request->input('role');
        $courtOwnerId = null;
        $permissions = null;

        if ($role === 'admin_assistant') {
            $modules = $request->input('modules');
            if ($modules === null) {
                $modules = User::defaultAssistantPermissions();
            }
            $permissions = array_values($modules);

            if ($request->filled('court_owner_id')) {
                $courtOwnerId = $request->input('court_owner_id');
            } elseif ($currentUser->isOwner()) {
                $courtOwnerId = $currentUser->id;
            } elseif ($currentUser->isAdminAssistant() && $currentUser->court_owner_id) {
                $courtOwnerId = $currentUser->court_owner_id;
            }
        }

        $user = User::create([
            'name' => trim($request->input('name')),
            'email' => strtolower(trim($request->input('email'))),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'role' => $role,
            'court_owner_id' => $courtOwnerId,
            'permissions' => $permissions,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Send registration confirmation email
        EmailNotificationService::sendRegistrationConfirmation($user);

        $roleTitle = match ($role) {
            'admin' => 'System Administrator',
            'court_owner' => 'Court Owner',
            'admin_assistant' => 'Admin Assistant',
            default => 'Player / Client',
        };

        return redirect()->route('owner.users.index')
            ->with('success', "{$roleTitle} '{$user->name}' created successfully!");
    }

    /**
     * Update User details, role, permissions, and active status
     */
    public function updateUser(Request $request, int $id)
    {
        $currentUser = Auth::user();
        $targetUser = User::findOrFail($id);

        // Security restrictions based on role hierarchy
        if ($currentUser->isAdminAssistant()) {
            if ($targetUser->isAdmin() || $targetUser->isOwner()) {
                abort(403, 'You do not have permission to edit administrators or court owners.');
            }
            $allowedRoles = ['client', 'admin_assistant'];
        } elseif ($currentUser->isOwner()) {
            if ($targetUser->isAdmin()) {
                abort(403, 'You do not have permission to edit system administrators.');
            }
            $allowedRoles = ['client', 'admin_assistant', 'court_owner'];
        } else {
            $allowedRoles = ['client', 'admin_assistant', 'court_owner', 'admin'];
        }

        $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $targetUser->id,
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string|in:' . implode(',', $allowedRoles),
            'court_owner_id' => 'nullable|exists:users,id',
            'modules' => 'nullable|array',
            'modules.*' => 'string|in:schedule,approvals,courts,photos,settings,users',
            'is_active' => 'nullable|boolean',
        ]);

        $newRole = $request->input('role');
        // Users cannot demote or change their own role
        if ($targetUser->id === $currentUser->id) {
            $newRole = $currentUser->role;
        }

        $updateData = [
            'name' => trim($request->input('name')),
            'email' => strtolower(trim($request->input('email'))),
            'phone' => $request->input('phone'),
            'role' => $newRole,
            // Users cannot deactivate their own active account
            'is_active' => $targetUser->id === $currentUser->id ? true : $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->input('password'));
        }

        if ($newRole === 'admin_assistant') {
            $updateData['permissions'] = array_values($request->input('modules', []));
            if ($request->filled('court_owner_id')) {
                $updateData['court_owner_id'] = $request->input('court_owner_id');
            } elseif ($currentUser->isOwner() && !$targetUser->court_owner_id) {
                $updateData['court_owner_id'] = $currentUser->id;
            }
        } else {
            $updateData['permissions'] = null;
            $updateData['court_owner_id'] = null;
        }

        $targetUser->update($updateData);

        return redirect()->route('owner.users.index')
            ->with('success', "User '{$targetUser->name}' updated successfully!");
    }

    /**
     * Toggle User active/inactive status
     */
    public function toggleUserStatus(int $id)
    {
        $currentUser = Auth::user();
        $targetUser = User::findOrFail($id);

        if ($targetUser->id === $currentUser->id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($currentUser->isAdminAssistant() && ($targetUser->isAdmin() || $targetUser->isOwner())) {
            abort(403, 'You do not have permission to modify this user account.');
        }

        if ($currentUser->isOwner() && $targetUser->isAdmin()) {
            abort(403, 'You do not have permission to modify a system administrator.');
        }

        $targetUser->update([
            'is_active' => !$targetUser->is_active,
        ]);

        $statusText = $targetUser->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "User '{$targetUser->name}' has been {$statusText}.");
    }

    /**
     * Delete a User
     */
    public function destroyUser(int $id)
    {
        $currentUser = Auth::user();
        $targetUser = User::findOrFail($id);

        if ($targetUser->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($targetUser->email === 'admin@paddlefield.com') {
            return back()->with('error', 'The primary system administrator account cannot be deleted.');
        }

        if ($currentUser->isAdminAssistant() && ($targetUser->isAdmin() || $targetUser->isOwner())) {
            abort(403, 'You do not have permission to delete this user.');
        }

        if ($currentUser->isOwner() && $targetUser->isAdmin()) {
            abort(403, 'You do not have permission to delete a system administrator.');
        }

        $name = $targetUser->name;
        $targetUser->delete();

        return back()->with('success', "User '{$name}' has been permanently deleted.");
    }

    /**
     * Reset a User's password to default (PaddleField2026!)
     */
    public function resetUserPassword(int $id)
    {
        $currentUser = Auth::user();
        $targetUser = User::findOrFail($id);

        if ($currentUser->isAdminAssistant() && ($targetUser->isAdmin() || $targetUser->isOwner())) {
            abort(403, 'You do not have permission to reset this user\'s password.');
        }

        if ($currentUser->isOwner() && $targetUser->isAdmin()) {
            abort(403, 'You do not have permission to reset a system administrator\'s password.');
        }

        $targetUser->update([
            'password' => Hash::make('PaddleField2026!'),
        ]);

        return back()->with('success', "Password for user '{$targetUser->name}' has been reset to 'PaddleField2026!'.");
    }

    /**
     * Retrieve held timeslots details for a specific user (Modal / AJAX)
     */
    public function userHeldSlots(int $id)
    {
        $user = User::findOrFail($id);

        $heldBookings = $user->bookings()
            ->whereIn('booking_status', ['held', 'expired', 'cancelled'])
            ->with(['court', 'slots'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'reference' => $b->booking_reference,
                    'court_name' => $b->court?->name ?? 'Paddle Court',
                    'booking_date' => $b->booking_date ? $b->booking_date->format('M d, Y') : '—',
                    'start_time' => $b->start_time,
                    'end_time' => $b->end_time,
                    'total_hours' => (int) $b->total_hours,
                    'slots' => $b->slots->pluck('slot_time')->toArray(),
                    'payment_method' => $b->payment_method,
                    'payment_status' => $b->payment_status,
                    'booking_status' => $b->booking_status,
                    'is_held' => $b->isHeld(),
                    'remaining_seconds' => $b->remaining_hold_seconds,
                    'total_amount' => (float) $b->total_amount,
                    'formatted_amount' => $b->formatted_amount,
                    'created_at' => $b->created_at ? $b->created_at->format('M d, Y h:i A') : '—',
                ];
            });

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?: '—',
                'role' => $user->role,
                'is_active' => (bool) $user->is_active,
            ],
            'total_held_slots' => $user->held_timeslots_count,
            'active_held_slots' => $user->active_held_slots_count,
            'held_bookings_count' => $user->held_bookings_count,
            'bookings' => $heldBookings,
        ]);
    }
}
