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

        return view('profile.edit', compact('user', 'settings', 'totalBookings', 'activeBookings', 'recentBookings'));
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
}
