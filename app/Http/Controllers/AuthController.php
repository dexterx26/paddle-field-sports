<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VenueSetting;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

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
            $user = Auth::user();

            // Require email confirmation before allowing login
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Please confirm your email address before logging in. We have sent a confirmation link to your email.',
                ])->with('unverified_email', $user->email)->onlyInput('email');
            }

            $request->session()->regenerate();

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
            // Ensure demo account has verified email
            if (!$user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

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
        if (Auth::check()) {
            return redirect()->route('home');
        }

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
            'email_verified_at' => null, // Requires confirmation before login
        ]);

        $verificationUrl = EmailNotificationService::generateVerificationUrl($user);

        // Dispatch welcome registration confirmation email with verification link
        EmailNotificationService::sendRegistrationConfirmation($user, $verificationUrl);

        $message = EmailNotificationService::isEnabled()
            ? 'Registration successful! Please check your email to confirm your account before logging in.'
            : 'Registration successful! Please confirm your email before logging in.';

        $redirect = redirect()->route('login')
            ->with('success', $message)
            ->with('unverified_email', $user->email);

        // In local environment when email sending is disabled, provide dev verification helper
        if (app()->isLocal() && !EmailNotificationService::isEnabled()) {
            $redirect->with('dev_verification_url', $verificationUrl);
        }

        return $redirect;
    }

    /**
     * Verify email via signed URL confirmation link.
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect()->route('login')->with('error', 'Invalid email confirmation link.');
        }

        if (!$request->hasValidSignature()) {
            return redirect()->route('login')
                ->with('error', 'This confirmation link has expired. Please request a new one below.')
                ->with('unverified_email', $user->email);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('info', 'Your email address is already confirmed! Please log in.');
        }

        $user->markEmailAsVerified();

        return redirect()->route('login')->with('success', 'Email confirmed successfully! You can now log in to your account.');
    }

    /**
     * Resend verification confirmation email.
     */
    public function resendConfirmation(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if ($user->hasVerifiedEmail()) {
                return redirect()->route('login')->with('info', 'Your email is already confirmed! Please log in.');
            }

            $verificationUrl = EmailNotificationService::generateVerificationUrl($user);
            EmailNotificationService::sendRegistrationConfirmation($user, $verificationUrl);

            $redirect = redirect()->route('login')
                ->with('success', 'A new confirmation email has been dispatched. Please check your inbox.')
                ->with('unverified_email', $user->email);

            if (app()->isLocal() && !EmailNotificationService::isEnabled()) {
                $redirect->with('dev_verification_url', $verificationUrl);
            }

            return $redirect;
        }

        return redirect()->route('login')->with('error', 'We could not find an account with that email address.');
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'You have been logged out.');
    }

    /**
     * Display forgot password request form.
     */
    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $settings = VenueSetting::getSettings();
        return view('auth.forgot-password', compact('settings'));
    }

    /**
     * Dispatch password reset link email.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('status', 'We have emailed your password reset link! Please check your inbox.');
        }

        $token = Password::broker()->createToken($user);
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        EmailNotificationService::sendPasswordReset($user, $resetUrl);

        $response = back()->with('status', 'We have emailed your password reset link! Please check your inbox.');

        if (app()->isLocal() && !EmailNotificationService::isEnabled()) {
            $response->with('dev_reset_url', $resetUrl);
        }

        return $response;
    }

    /**
     * Display reset password form.
     */
    public function showResetPassword(Request $request, $token)
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }

        $email = $request->query('email', old('email'));
        $settings = VenueSetting::getSettings();

        return view('auth.reset-password', compact('token', 'email', 'settings'));
    }

    /**
     * Reset user password using token.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ]);

                if (!$user->hasVerifiedEmail()) {
                    $user->markEmailAsVerified();
                }

                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Your password has been reset successfully! You can now sign in with your new password.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}
