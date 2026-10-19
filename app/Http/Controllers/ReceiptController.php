<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CreditTopup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    /**
     * Securely stream a booking payment receipt proof image.
     * Enforces RA 10173 authorization: only the booking owner, court owners,
     * or authorized admin assistants can view the uploaded receipt screenshot.
     */
    public function showBookingReceipt(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);

        if (!$this->isAuthorizedForBooking($request, $booking)) {
            abort(403, 'Unauthorized access: You do not have permission to view this payment receipt proof under RA 10173.');
        }

        if (empty($booking->receipt_image_path)) {
            abort(404, 'Receipt not found.');
        }

        return $this->streamReceiptFile($booking->receipt_image_path);
    }

    /**
     * Securely stream a credit wallet top-up receipt proof image.
     * Enforces RA 10173 authorization: only the topup owner, court owners,
     * or authorized admin assistants can view the uploaded receipt screenshot.
     */
    public function showCreditReceipt(Request $request, int $id)
    {
        $topup = CreditTopup::findOrFail($id);

        if (!$this->isAuthorizedForCreditTopup($request, $topup)) {
            abort(403, 'Unauthorized access: You do not have permission to view this payment receipt proof under RA 10173.');
        }

        if (empty($topup->receipt_image_path)) {
            abort(404, 'Receipt not found.');
        }

        return $this->streamReceiptFile($topup->receipt_image_path);
    }

    /**
     * Verify whether the current request is authorized to view a booking receipt.
     */
    protected function isAuthorizedForBooking(Request $request, Booking $booking): bool
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isAdmin() || $user->isOwner()) {
                return true;
            }
            if ($user->isAdminAssistant() && ($user->hasPermission('approvals') || $user->hasPermission('all_bookings'))) {
                return true;
            }
            if ($booking->user_id && $booking->user_id === $user->id) {
                return true;
            }
        }

        // Allow guest who completed this booking in the current session
        $lastBookedRef = session('last_booked_ref');
        $lastBookedId = session('last_booked_id');
        if ($lastBookedRef === $booking->booking_reference || $lastBookedId === $booking->id) {
            return true;
        }

        // Allow signed URLs (e.g. for temporary customer review)
        if ($request->hasValidSignature()) {
            return true;
        }

        return false;
    }

    /**
     * Verify whether the current request is authorized to view a credit topup receipt.
     */
    protected function isAuthorizedForCreditTopup(Request $request, CreditTopup $topup): bool
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isAdmin() || $user->isOwner()) {
                return true;
            }
            if ($user->isAdminAssistant() && ($user->hasPermission('approvals') || $user->hasPermission('all_bookings'))) {
                return true;
            }
            if ($topup->user_id === $user->id) {
                return true;
            }
        }

        if ($request->hasValidSignature()) {
            return true;
        }

        return false;
    }

    /**
     * Find and securely stream the receipt image file with no-index / no-cache headers.
     */
    protected function streamReceiptFile(string $storedPath)
    {
        $normalized = ltrim($storedPath, '/');

        // Check private storage locations first
        $candidatePaths = [
            storage_path('app/' . $normalized),
            storage_path('app/private/' . $normalized),
            storage_path('app/public/' . $normalized),
        ];

        $resolvedPath = null;
        foreach ($candidatePaths as $path) {
            if (file_exists($path) && is_file($path)) {
                $resolvedPath = $path;
                break;
            }
        }

        if (!$resolvedPath) {
            abort(404, 'Receipt image file not found on disk.');
        }

        $mime = mime_content_type($resolvedPath) ?: 'image/jpeg';

        return response()->file($resolvedPath, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive, nosnippet',
        ]);
    }
}
