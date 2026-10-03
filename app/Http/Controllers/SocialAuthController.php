<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Supported social providers.
     */
    protected array $supportedProviders = ['google', 'facebook'];

    /**
     * Check if real provider credentials are fully configured.
     */
    protected function isProviderConfigured(string $provider): bool
    {
        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        return !empty($clientId) && !empty($clientSecret);
    }

    /**
     * Redirect to the provider's OAuth page (or simulation mode if keys not set).
     */
    public function redirect(Request $request, string $provider)
    {
        if (!in_array($provider, $this->supportedProviders)) {
            return redirect()->route('login')->with('error', 'Unsupported social login provider.');
        }

        // If credentials are configured, initiate real OAuth redirect via Laravel Socialite
        if ($this->isProviderConfigured($provider)) {
            try {
                return Socialite::driver($provider)->stateless()->redirect();
            } catch (\Throwable $e) {
                return redirect()->route('login')->with('error', 'OAuth error: ' . $e->getMessage());
            }
        }

        // Otherwise, open the interactive Simulation / Dev Mock screen
        return redirect()->route('auth.social.mock', ['provider' => $provider]);
    }

    /**
     * Handle the OAuth callback from the provider.
     */
    public function callback(Request $request, string $provider)
    {
        if (!in_array($provider, $this->supportedProviders)) {
            return redirect()->route('login')->with('error', 'Unsupported social login provider.');
        }

        try {
            // Use stateless() to prevent session state drops across cross-domain redirects & reverse proxies
            $socialUser = Socialite::driver($provider)->stateless()->user();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Social auth callback failed for provider {$provider}: " . $e->getMessage());
            return redirect()->route('login')->with('error', "Could not authenticate with " . ucfirst($provider) . ": " . $e->getMessage());
        }

        return $this->loginOrRegisterSocialUser($provider, [
            'id' => $socialUser->getId(),
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? ucfirst($provider) . ' User',
            'email' => $socialUser->getEmail(),
            'avatar' => $socialUser->getAvatar(),
        ]);
    }

    /**
     * Dev Simulation Screen (shown when real API credentials are not yet added to .env)
     */
    public function showMock(Request $request, string $provider)
    {
        if (!in_array($provider, $this->supportedProviders)) {
            return redirect()->route('login')->with('error', 'Unsupported social login provider.');
        }

        $providerName = $provider === 'google' ? 'Google / Gmail' : 'Facebook';
        $providerIcon = $provider === 'google' ? 'fa-brands fa-google text-rose-500' : 'fa-brands fa-facebook text-blue-500';

        return view('auth.social-mock', [
            'provider' => $provider,
            'providerName' => $providerName,
            'providerIcon' => $providerIcon,
            'appUrl' => config('app.url'),
        ]);
    }

    /**
     * Process simulated login/registration for local development testing.
     */
    public function processMock(Request $request, string $provider)
    {
        if (!in_array($provider, $this->supportedProviders)) {
            return redirect()->route('login')->with('error', 'Unsupported social login provider.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $mockId = 'sim_' . Str::random(12);
        $mockAvatar = "https://ui-avatars.com/api/?name=" . urlencode($validated['name']) . "&background=random";

        return $this->loginOrRegisterSocialUser($provider, [
            'id' => $mockId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'avatar' => $mockAvatar,
        ]);
    }

    /**
     * Core logic to register a new user or log in an existing user from social credentials.
     */
    protected function loginOrRegisterSocialUser(string $provider, array $profileData)
    {
        $idField = $provider === 'google' ? 'google_id' : 'facebook_id';
        $providerId = $profileData['id'];
        $email = $profileData['email'];
        $name = $profileData['name'];
        $avatar = $profileData['avatar'] ?? null;

        // 1. Check if user already exists with this provider ID
        $user = User::where($idField, $providerId)->first();

        // 2. If not found by provider ID, check by email
        if (!$user && !empty($email)) {
            $user = User::where('email', $email)->first();
            if ($user) {
                // Link this social provider to existing account
                $user->update([
                    $idField => $providerId,
                    'avatar' => $user->avatar ?: $avatar,
                ]);
            }
        }

        // 3. If still no user, create a new registered Client (Player)
        $isNewUser = false;
        if (!$user) {
            $isNewUser = true;
            $user = User::create([
                'name' => $name,
                'email' => $email ?: ($providerId . "@{$provider}.user"),
                'role' => 'client',
                'phone' => null, // Optional for social accounts
                'password' => null, // No password needed for OAuth sign-ins
                $idField => $providerId,
                'avatar' => $avatar,
                'is_active' => true,
            ]);

            // Dispatch welcome confirmation email to new player
            EmailNotificationService::sendRegistrationConfirmation($user);
        }

        // Log the user into Laravel session
        Auth::login($user, true);
        request()->session()->regenerate();

        // Clear lingering auth URLs from session to prevent redirect loops
        $intendedUrl = session()->get('url.intended');
        if ($intendedUrl && (str_contains($intendedUrl, '/login') || str_contains($intendedUrl, '/register') || str_contains($intendedUrl, '/auth/'))) {
            session()->forget('url.intended');
        }

        $actionWord = $isNewUser ? 'registered and logged in' : 'logged in';
        $providerTitle = ucfirst($provider);

        // Redirect based on user role
        if ($user->isStaffOrAdmin()) {
            if ($user->isAdminAssistant()) {
                return redirect()->intended(route($user->getFirstAllowedRoute()))
                    ->with('success', "Welcome back, {$user->name}! ({$providerTitle} sign-in)");
            }
            return redirect()->intended(route('owner.dashboard'))
                ->with('success', "Welcome back, {$user->name}! ({$providerTitle} sign-in)");
        }

        return redirect()->intended(route('home'))
            ->with('success', "You have successfully {$actionWord} with {$providerTitle}!");
    }
}
