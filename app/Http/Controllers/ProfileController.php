<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile and security screen.
     */
    public function edit()
    {
        /** @var User $user */
        $user = Auth::user();
        $settings = VenueSetting::getSettings();

        // Count user bookings
        $totalBookings = Booking::where(function ($q) use ($user) {
            $q->where('user_id', $user->id);
            if (!empty($user->email)) {
                $q->orWhere('customer_email', $user->email);
            }
        })->count();

        $activeBookings = Booking::where(function ($q) use ($user) {
            $q->where('user_id', $user->id);
            if (!empty($user->email)) {
                $q->orWhere('customer_email', $user->email);
            }
        })
        ->whereIn('booking_status', ['confirmed', 'held', 'pending_approval'])
        ->whereDate('booking_date', '>=', now()->toDateString())
        ->count();

        $recentBookings = Booking::where(function ($q) use ($user) {
            $q->where('user_id', $user->id);
            if (!empty($user->email)) {
                $q->orWhere('customer_email', $user->email);
            }
        })
        ->with(['court', 'slots'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        $creditTopups = $user->creditTopups()->limit(10)->get();
        $creditTransactions = $user->creditTransactions()->limit(10)->get();

        return view('profile.edit', compact(
            'user',
            'settings',
            'totalBookings',
            'activeBookings',
            'recentBookings',
            'creditTopups',
            'creditTransactions'
        ));
    }

    /**
     * Update the user's profile details (Name, Email, Phone, Avatar).
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ]);

        $updateData = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
        ];

        // Handle Avatar Removal
        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                Storage::disk('public')->delete($user->avatar);
            }
            $updateData['avatar'] = null;
        }

        // Handle Avatar Upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $updateData['avatar'] = $path;
        }

        $user->update($updateData);

        return redirect()->route('profile.edit')->with('success', 'Your profile details have been updated successfully!');
    }

    /**
     * Update the user's account password.
     */
    public function updatePassword(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // If the user already has a password set, require existing password
        if ($user->hasPassword()) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $request->validate($rules, [
            'current_password.current_password' => 'The provided current password does not match your existing password.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'Your new password must be at least 8 characters long.',
        ]);

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Your password has been changed successfully!');
    }

    /**
     * RA 10173 Section 18: Right to Data Portability
     * Export all personal data associated with the user account in structured JSON format.
     */
    public function exportData()
    {
        /** @var User $user */
        $user = Auth::user();

        $bookings = $user->bookings()
            ->with(['court:id,name,surface_type', 'slots:id,booking_id,slot_time'])
            ->orderByDesc('booking_date')
            ->get()
            ->map(function ($b) {
                return [
                    'reference' => $b->booking_reference,
                    'court' => $b->court?->name,
                    'date' => $b->booking_date ? $b->booking_date->format('Y-m-d') : null,
                    'start_time' => $b->start_time,
                    'end_time' => $b->end_time,
                    'total_hours' => $b->total_hours,
                    'total_amount' => (float) $b->total_amount,
                    'payment_method' => $b->payment_method,
                    'status' => $b->booking_status,
                    'created_at' => $b->created_at ? $b->created_at->toISOString() : null,
                ];
            });

        $creditTopups = $user->creditTopups()
            ->orderByDesc('id')
            ->get()
            ->map(function ($t) {
                return [
                    'reference' => $t->reference,
                    'amount_php' => (float) $t->amount_php,
                    'credits' => (float) $t->credits,
                    'status' => $t->status,
                    'created_at' => $t->created_at ? $t->created_at->toISOString() : null,
                ];
            });

        $creditTransactions = $user->creditTransactions()
            ->orderByDesc('id')
            ->get()
            ->map(function ($tx) {
                return [
                    'type' => $tx->type,
                    'amount_credits' => (float) $tx->amount_credits,
                    'balance_after' => (float) $tx->balance_after,
                    'description' => $tx->description,
                    'created_at' => $tx->created_at ? $tx->created_at->toISOString() : null,
                ];
            });

        $exportData = [
            'export_metadata' => [
                'legal_basis' => 'Republic Act No. 10173 (Data Privacy Act of 2012) – Section 18 Data Portability',
                'facility' => 'Paddle Field Sports Center',
                'generated_at' => now()->toIso8601String(),
                'data_subject_id' => $user->id,
            ],
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'credits_balance' => (float) ($user->credits ?? 0),
                'email_verified_at' => $user->email_verified_at ? $user->email_verified_at->toISOString() : null,
                'created_at' => $user->created_at ? $user->created_at->toISOString() : null,
            ],
            'court_reservations' => $bookings,
            'credit_topups' => $creditTopups,
            'credit_transactions_ledger' => $creditTransactions,
        ];

        $filename = 'paddlefield_data_export_' . $user->id . '_' . date('Ymd_His') . '.json';

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * RA 10173 Section 16(e): Right to Erasure or Blocking
     * Allow self-service account deletion and anonymization of personal identifiers.
     */
    public function destroyAccount(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->isOwner() || $user->isAdmin()) {
            return back()->with('error', 'Court owners and administrators cannot self-delete accounts. Please transfer administrative role or contact organization management.');
        }

        // Require password confirmation if user has a password set
        if ($user->hasPassword()) {
            $request->validate([
                'delete_confirmation_password' => ['required', 'current_password'],
            ], [
                'delete_confirmation_password.current_password' => 'The password you entered is incorrect. Account deletion aborted.',
            ]);
        }

        $userId = $user->id;

        // Clean up avatar file if stored
        if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
            try {
                Storage::disk('public')->delete($user->avatar);
            } catch (\Throwable $e) {}
        }

        // De-identify and anonymize user personal data per RA 10173 retention principles
        $user->name = 'Anonymized User #' . $userId;
        $user->email = 'anonymized_' . $userId . '_' . time() . '@paddlefield.local';
        $user->phone = null;
        $user->avatar = null;
        $user->google_id = null;
        $user->facebook_id = null;
        $user->is_active = false;
        $user->deactivation_reason = 'Personal data erasure requested by account holder under RA 10173 on ' . now()->toDateTimeString();
        $user->save();

        // De-identify historical booking PII attached to this user
        $user->bookings()->update([
            'customer_name' => 'Anonymized Player',
            'customer_phone' => '09000000000',
            'customer_email' => null,
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'Your account has been deleted and your personal identifiers have been anonymized pursuant to Republic Act No. 10173.');
    }
}
