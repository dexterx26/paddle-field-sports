<?php

namespace Tests\Feature;

use App\Mail\RegistrationSuccessfulMail;
use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserProfileAndRegistrationEmailTest extends TestCase
{
    use RefreshDatabase;

    protected User $client;
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->client = User::where('role', 'client')->first();
        $this->owner = User::where('role', 'court_owner')->first();
    }

    public function test_guest_is_redirected_from_profile_page(): void
    {
        $response = $this->get(route('profile.edit'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_profile_page(): void
    {
        $response = $this->actingAs($this->client)->get(route('profile.edit'));

        $response->assertStatus(200);
        $response->assertSee($this->client->name);
        $response->assertSee($this->client->email);
        $response->assertSee('Account & Profile Settings', false);
        $response->assertSee('Personal Information');
        $response->assertSee('Password & Security', false);
    }

    public function test_user_can_update_profile_details(): void
    {
        $response = $this->actingAs($this->client)->put(route('profile.update'), [
            'name' => 'Updated Juan Dela Cruz',
            'email' => 'updated.juan@example.com',
            'phone' => '0918-999-8888',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $this->client->refresh();
        $this->assertEquals('Updated Juan Dela Cruz', $this->client->name);
        $this->assertEquals('updated.juan@example.com', $this->client->email);
        $this->assertEquals('0918-999-8888', $this->client->phone);
    }

    public function test_user_can_upload_and_remove_avatar(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('profile_photo.jpg', 200, 200);

        // Upload avatar
        $response = $this->actingAs($this->client)->put(route('profile.update'), [
            'name' => $this->client->name,
            'email' => $this->client->email,
            'phone' => $this->client->phone,
            'avatar' => $file,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->client->refresh();
        $this->assertNotNull($this->client->avatar);
        Storage::disk('public')->assertExists($this->client->avatar);

        // Remove avatar
        $removeResponse = $this->actingAs($this->client)->put(route('profile.update'), [
            'name' => $this->client->name,
            'email' => $this->client->email,
            'remove_avatar' => '1',
        ]);

        $removeResponse->assertRedirect(route('profile.edit'));
        $this->client->refresh();
        $this->assertNull($this->client->avatar);
    }

    public function test_user_can_change_password_with_valid_current_password(): void
    {
        $this->client->update(['password' => Hash::make('oldpassword123')]);

        $response = $this->actingAs($this->client)->put(route('profile.password.update'), [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $this->client->refresh();
        $this->assertTrue(Hash::check('newpassword456', $this->client->password));
    }

    public function test_user_cannot_change_password_with_incorrect_current_password(): void
    {
        $this->client->update(['password' => Hash::make('correctpassword')]);

        $response = $this->actingAs($this->client)->put(route('profile.password.update'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->client->refresh();
        $this->assertTrue(Hash::check('correctpassword', $this->client->password));
    }

    public function test_oauth_user_without_password_can_set_new_password_directly(): void
    {
        // Socialite user created without a password
        $oauthUser = User::create([
            'name' => 'OAuth Player',
            'email' => 'oauth.player@gmail.com',
            'role' => 'client',
            'google_id' => 'google_test_12345',
            'password' => null,
            'is_active' => true,
        ]);

        $this->assertFalse($oauthUser->hasPassword());

        // Should not require current_password
        $response = $this->actingAs($oauthUser)->put(route('profile.password.update'), [
            'password' => 'firstpassword789',
            'password_confirmation' => 'firstpassword789',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('success');

        $oauthUser->refresh();
        $this->assertTrue($oauthUser->hasPassword());
        $this->assertTrue(Hash::check('firstpassword789', $oauthUser->password));
    }

    public function test_registration_sends_welcome_confirmation_email_when_enabled(): void
    {
        Mail::fake();
        config(['mail.enabled' => true]);

        $userData = [
            'name' => 'Alex Morgan',
            'email' => 'alex.morgan@example.com',
            'phone' => '0917-888-9999',
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect(route('login'));
        $this->assertGuest();

        // Verify email was dispatched
        Mail::assertSent(RegistrationSuccessfulMail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']) &&
                   $mail->user->email === $userData['email'] &&
                   $mail->user->name === $userData['name'] &&
                   !empty($mail->verificationUrl);
        });
    }

    public function test_registration_safely_skips_email_when_email_sending_disabled(): void
    {
        Mail::fake();
        config(['mail.enabled' => false]);

        $userData = [
            'name' => 'Quiet Player',
            'email' => 'quiet.player@example.com',
            'phone' => '0917-000-1111',
            'password' => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertRedirect(route('login'));
        $this->assertGuest();

        // Verify no email was dispatched
        Mail::assertNothingSent();
    }


    public function test_navigation_header_shows_profile_link_for_authenticated_users(): void
    {
        // Public home page when logged in as client
        $response = $this->actingAs($this->client)->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee(route('profile.edit'));
        $response->assertSee('My Profile & Password', false);

        // Owner portal when logged in as owner
        $ownerResponse = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee(route('profile.edit'));
        $ownerResponse->assertSee('My Profile & Password', false);
    }

    public function test_mail_test_command_detects_missing_credentials(): void
    {
        config([
            'mail.mailers.smtp.username' => null,
            'mail.mailers.smtp.password' => null,
        ]);

        $this->artisan('mail:test')
            ->expectsOutputToContain('Gmail SMTP authentication credentials are missing in your .env file!')
            ->assertExitCode(1);
    }

    public function test_mail_test_command_sends_email_when_credentials_present(): void
    {
        Mail::fake();

        config([
            'mail.mailers.smtp.username' => 'test@gmail.com',
            'mail.mailers.smtp.password' => 'secret16charpass',
        ]);

        $this->artisan('mail:test test.player@example.com --template=welcome')
            ->expectsOutputToContain('SUCCESS! The email was accepted by')
            ->assertExitCode(0);

        Mail::assertSent(RegistrationSuccessfulMail::class);
    }
}

