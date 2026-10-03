<?php

namespace Tests\Feature;

use App\Mail\RegistrationSuccessfulMail;
use App\Models\User;
use App\Models\VenueSetting;
use App\Services\EmailNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationEmailConfirmationAndGoogleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        VenueSetting::create([
            'venue_name' => 'Paddle Field Sports Center',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
        ]);
    }

    public function test_google_button_and_dividers_are_hidden_when_google_redirect_uri_is_not_set(): void
    {
        config(['services.google.redirect_uri' => null]);

        // Login page
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertStatus(200);
        $loginResponse->assertDontSee('Continue with Google');
        $loginResponse->assertDontSee('or sign in with credentials');

        // Register page
        $registerResponse = $this->get(route('register'));
        $registerResponse->assertStatus(200);
        $registerResponse->assertDontSee('Continue with Google');
        $registerResponse->assertDontSee('or register with email');

        // Terms and privacy must still be visible
        $registerResponse->assertSee('Terms of Service');
        $registerResponse->assertSee('Privacy Policy');

        // Direct visit to google redirect returns error
        $redirectResponse = $this->get(route('auth.social.redirect', 'google'));
        $redirectResponse->assertRedirect(route('login'));
        $redirectResponse->assertSessionHas('error', 'Google sign-in is not configured on this environment.');
    }

    public function test_google_button_and_dividers_are_shown_when_google_redirect_uri_is_configured(): void
    {
        config(['services.google.redirect_uri' => 'http://127.0.0.1:8000/auth/google/callback']);

        // Login page
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Continue with Google');
        $loginResponse->assertSee('or sign in with credentials');

        // Register page
        $registerResponse = $this->get(route('register'));
        $registerResponse->assertStatus(200);
        $registerResponse->assertSee('Continue with Google');
        $registerResponse->assertSee('or register with email');
    }

    public function test_registration_requires_email_confirmation_before_login(): void
    {
        Mail::fake();
        config(['mail.enabled' => true]);

        $userData = [
            'name' => 'Carlos Yulo',
            'email' => 'carlos.yulo@example.com',
            'phone' => '0917-555-1234',
            'password' => 'Gymnast2026!',
            'password_confirmation' => 'Gymnast2026!',
        ];

        // 1. Submit registration
        $registerResponse = $this->post(route('register'), $userData);
        $registerResponse->assertRedirect(route('login'));
        $registerResponse->assertSessionHas('unverified_email', 'carlos.yulo@example.com');
        $this->assertGuest();

        // 2. Check user created in database with null email_verified_at
        $user = User::where('email', 'carlos.yulo@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        // 3. Verify confirmation email was sent with a verification URL
        $sentVerificationUrl = null;
        Mail::assertSent(RegistrationSuccessfulMail::class, function ($mail) use (&$sentVerificationUrl) {
            $sentVerificationUrl = $mail->verificationUrl;
            return !empty($sentVerificationUrl);
        });

        // 4. Try logging in BEFORE confirming email -> should be blocked!
        $loginAttempt = $this->post(route('login'), [
            'email' => 'carlos.yulo@example.com',
            'password' => 'Gymnast2026!',
        ]);

        $loginAttempt->assertSessionHasErrors('email');
        $loginAttempt->assertSessionHas('unverified_email', 'carlos.yulo@example.com');
        $this->assertGuest();

        // 5. Follow the verification URL to confirm email
        $verifyResponse = $this->get($sentVerificationUrl);
        $verifyResponse->assertRedirect(route('login'));
        $verifyResponse->assertSessionHas('success');

        // 6. User is now marked as verified
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        // 7. Now log in with confirmed credentials -> SUCCESS!
        $successfulLogin = $this->post(route('login'), [
            'email' => 'carlos.yulo@example.com',
            'password' => 'Gymnast2026!',
        ]);

        $successfulLogin->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_resend_confirmation_email(): void
    {
        Mail::fake();
        config(['mail.enabled' => true]);

        $user = User::create([
            'name' => 'Unconfirmed Player',
            'email' => 'unconfirmed@example.com',
            'phone' => '0917-111-2222',
            'password' => Hash::make('Secret123!'),
            'role' => 'client',
            'email_verified_at' => null,
        ]);

        $response = $this->post(route('verification.resend'), [
            'email' => 'unconfirmed@example.com',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        Mail::assertSent(RegistrationSuccessfulMail::class, function ($mail) {
            return $mail->hasTo('unconfirmed@example.com') && !empty($mail->verificationUrl);
        });
    }

    public function test_invalid_confirmation_link_is_rejected(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test.tampered@example.com',
            'phone' => '0917-000-0000',
            'password' => Hash::make('Secret123!'),
            'role' => 'client',
            'email_verified_at' => null,
        ]);

        // Tampered URL
        $tamperedUrl = route('verification.verify', [
            'id' => $user->id,
            'hash' => 'invalid_hash_value',
            'signature' => 'invalid_signature',
        ]);

        $response = $this->get($tamperedUrl);
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
        $this->assertNull($user->fresh()->email_verified_at);
    }
}
