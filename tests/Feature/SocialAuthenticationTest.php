<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SocialAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_page_displays_google_and_facebook_options(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('Continue with Google');
        $response->assertSee('Continue with Facebook');
        $response->assertSee(route('auth.social.redirect', 'google'));
        $response->assertSee(route('auth.social.redirect', 'facebook'));
    }

    public function test_login_page_displays_google_option_and_hides_facebook(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Continue with Google');
        $response->assertDontSee('Continue with Facebook');
        $response->assertSee(route('auth.social.redirect', 'google'));
        $response->assertDontSee(route('auth.social.redirect', 'facebook'));
    }

    public function test_social_redirect_routes_to_mock_when_keys_are_unconfigured(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
            'services.facebook.client_id' => null,
            'services.facebook.client_secret' => null,
        ]);

        $response = $this->get(route('auth.social.redirect', 'google'));
        $response->assertRedirect(route('auth.social.mock', 'google'));

        $responseFb = $this->get(route('auth.social.redirect', 'facebook'));
        $responseFb->assertRedirect(route('auth.social.mock', 'facebook'));
    }

    public function test_social_mock_view_renders_correctly(): void
    {
        $response = $this->get(route('auth.social.mock', 'google'));

        $response->assertStatus(200);
        $response->assertSee('Google / Gmail Sign-In');
        $response->assertSee('Sandbox / Dev Simulation Mode');
        $response->assertSee('Marcus Player');
    }

    public function test_new_user_can_register_via_social_auth_without_phone_or_password(): void
    {
        $response = $this->post(route('auth.social.mock.process', 'google'), [
            'name' => 'Samantha Reed',
            'email' => 'samantha.reed@gmail.com',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'samantha.reed@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Samantha Reed', $user->name);
        $this->assertEquals('client', $user->role);
        $this->assertNull($user->phone); // Phone is optional for social users
        $this->assertNull($user->password); // No password required
        $this->assertNotNull($user->google_id);
    }

    public function test_existing_user_with_same_email_is_linked_to_social_account(): void
    {
        $existing = User::create([
            'name' => 'Carlos Lopez',
            'email' => 'carlos@example.com',
            'phone' => '09171234567',
            'role' => 'client',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post(route('auth.social.mock.process', 'facebook'), [
            'name' => 'Carlos Lopez FB',
            'email' => 'carlos@example.com',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($existing);

        $existing->refresh();
        $this->assertNotNull($existing->facebook_id);
        $this->assertEquals('09171234567', $existing->phone); // Original phone preserved
    }

    public function test_invalid_provider_returns_error(): void
    {
        $response = $this->get('/auth/twitter/redirect');
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }
}
