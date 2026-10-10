<?php

namespace Tests\Feature;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
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

    public function test_login_page_renders_forgot_password_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Forgot password?');
        $response->assertSee(route('password.request'));
    }

    public function test_forgot_password_page_renders_properly(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Forgot Password');
        $response->assertSee('Send Reset Link');
        $response->assertSee(route('password.email'));
        $response->assertSee(route('login'));
    }

    public function test_authenticated_user_is_redirected_away_from_forgot_password(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('password.request'));

        $response->assertRedirect(route('home'));
    }

    public function test_reset_link_dispatched_for_existing_user(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'player@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'player@example.com',
        ]);

        $response->assertSessionHas('status');

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'player@example.com',
        ]);
    }

    public function test_non_existent_email_shows_friendly_status_without_error(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'nonexistent@example.com',
        ]);
    }

    public function test_reset_password_page_renders_with_token(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Set New Password');
        $response->assertSee($user->email);
        $response->assertSee($token);
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => Hash::make('OldPassword123!'),
            'email_verified_at' => null, // Was unverified
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'player@example.com',
            'password' => 'NewPassword2026!',
            'password_confirmation' => 'NewPassword2026!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user->refresh();

        // Password hash updated
        $this->assertTrue(Hash::check('NewPassword2026!', $user->password));

        // Token consumed
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'player@example.com',
        ]);

        // Email also verified automatically
        $this->assertNotNull($user->email_verified_at);

        // Can log in with new password
        $loginResponse = $this->post(route('login'), [
            'email' => 'player@example.com',
            'password' => 'NewPassword2026!',
        ]);

        $loginResponse->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'password' => Hash::make('OriginalPassword123!'),
        ]);

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token-12345',
            'email' => 'player@example.com',
            'password' => 'NewPassword2026!',
            'password_confirmation' => 'NewPassword2026!',
        ]);

        $response->assertSessionHasErrors('email');

        $user->refresh();
        $this->assertTrue(Hash::check('OriginalPassword123!', $user->password));
    }

    public function test_password_reset_fails_when_confirmation_does_not_match(): void
    {
        $user = User::factory()->create([
            'email' => 'player@example.com',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'player@example.com',
            'password' => 'NewPassword2026!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
