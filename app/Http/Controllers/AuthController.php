<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VenueSetting;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        $settings = VenueSetting::getSettings();
        return view('auth.login', compact('settings'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->isAdminAssistant() && !$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'Your admin assistant account has been deactivated. Please contact the court owner.',
                ])->onlyInput('email');
            }

            if ($user->isStaffOrAdmin()) {
                if ($user->isAdminAssistant()) {
                    return redirect()->intended(route($user->getFirstAllowedRoute()));
                }
                return redirect()->intended(route('owner.dashboard'));
            }

            return redirect()->intended(route('home'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Demo login helper for fast testing
     */
    public function quickLogin(string $role)
    {
        $emailMap = [
            'owner' => 'owner@paddlefield.com',
            'admin' => 'admin@paddlefield.com',
            'assistant' => 'assistant@paddlefield.com',
            'player' => 'player@gmail.com',
            'player2' => 'player2@gmail.com',
            'paolo' => 'paolo@gmail.com',
            'paolosoriano' => 'paolo@gmail.com',
        ];

        if (!isset($emailMap[$role])) {
            return redirect()->route('login');
        }

        $user = User::where('email', $emailMap[$role])->first();

        if ($user) {
            if ($user->isAdminAssistant() && !$user->is_active) {
                return redirect()->route('login')->with('error', 'Demo assistant account is currently inactive.');
            }

            Auth::login($user);
            request()->session()->regenerate();

            if ($user->isStaffOrAdmin()) {
                if ($user->isAdminAssistant()) {
                    return redirect()->route($user->getFirstAllowedRoute())->with('success', "Logged in as {$user->name}");
                }
                return redirect()->route('owner.dashboard')->with('success', "Logged in as {$user->name}");
            }

            return redirect()->route('home')->with('success', "Logged in as {$user->name}");
        }

        return redirect()->route('login')->with('error', 'Demo user not found.');
    }

    public function showRegister()
    {
        $settings = VenueSetting::getSettings();
        return view('auth.register', compact('settings'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:25',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'client', // standard client registration
        ]);

        // Dispatch welcome registration confirmation email
        EmailNotificationService::sendRegistrationConfirmation($user);

        Auth::login($user);
        $request->session()->regenerate();

        $message = EmailNotificationService::isEnabled()
            ? 'Registration successful! A welcome confirmation email has been dispatched to your email.'
            : 'Registration successful! Welcome to ' . config('app.name', 'Paddle Field Sports Center') . '.';

        return redirect()->route('home')->with('success', $message);
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'You have been logged out.');
    }
}
