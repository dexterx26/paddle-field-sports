<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PickleballVenueLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $client;
    protected VenueSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->owner = User::where('role', 'court_owner')->first();
        $this->client = User::where('role', 'client')->first();
        $this->settings = VenueSetting::getSettings();
    }

    public function test_settings_page_renders_logo_upload_section_and_previews(): void
    {
        $response = $this->actingAs($this->owner)->get(route('owner.settings'));

        $response->assertStatus(200);
        $response->assertSee('Pickleball Court & Venue Logo', false);
        $response->assertSee('Browser Tab Icon (Favicon)');
        $response->assertSee('Navigation Header Logo');
        $response->assertSee('name="logo"', false);
    }

    public function test_court_owner_can_upload_venue_logo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('pickleball-venue-logo.png', 400, 400);

        $response = $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Paddle Field Sports Center',
            'tagline' => 'Premier Indoor Pickleball Facility',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'credit_conversion_rate' => 150,
            'credits_enabled' => 1,
            'logo' => $file,
        ]);

        $response->assertRedirect(route('owner.settings'));
        $response->assertSessionHas('success');

        $this->settings->refresh();
        $this->assertNotNull($this->settings->logo_path);
        $this->assertTrue(str_starts_with($this->settings->logo_path, 'logos/'));
        Storage::disk('public')->assertExists($this->settings->logo_path);
    }

    public function test_uploaded_venue_logo_is_displayed_as_tab_icon_and_navigation_logo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('my-pickleball-logo.png', 300, 300);

        $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Dreamland Pickleball Arena',
            'tagline' => 'Championship Acrylic Courts',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'credit_conversion_rate' => 150,
            'credits_enabled' => 1,
            'logo' => $file,
        ]);

        $this->settings->refresh();
        $expectedLogoUrl = $this->settings->logo_url;

        // 1. Check Public Landing Page (layouts.app)
        auth()->logout();
        $publicResponse = $this->get(route('home'));
        $publicResponse->assertStatus(200);
        // Browser tab icon on top of tab (<link rel="icon">)
        $publicResponse->assertSee('<link rel="icon" type="image/svg+xml" href="' . $expectedLogoUrl . '"', false);
        // Navigation header brand logo
        $publicResponse->assertSee($expectedLogoUrl, false);
        $publicResponse->assertSee('Dreamland Pickleball Arena');

        // 2. Check Owner Portal Layout (layouts.owner)
        $ownerResponse = $this->actingAs($this->owner)->get(route('owner.dashboard'));
        $ownerResponse->assertStatus(200);
        $ownerResponse->assertSee('<link rel="icon" type="image/svg+xml" href="' . $expectedLogoUrl . '"', false);
        $ownerResponse->assertSee($expectedLogoUrl, false);

        // 3. Check Login Screen (auth.login) for guest
        auth()->logout();
        $loginResponse = $this->get(route('login'));
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee($expectedLogoUrl, false);
    }

    public function test_court_owner_can_remove_uploaded_venue_logo(): void
    {
        Storage::fake('public');

        // First upload a logo
        $file = UploadedFile::fake()->image('court-logo-temp.png', 200, 200);
        $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Paddle Field Sports Center',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'logo' => $file,
        ]);

        $this->settings->refresh();
        $oldPath = $this->settings->logo_path;
        $this->assertNotNull($oldPath);
        Storage::disk('public')->assertExists($oldPath);

        // Now remove it
        $removeResponse = $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Paddle Field Sports Center',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'remove_logo' => 1,
        ]);

        $removeResponse->assertRedirect(route('owner.settings'));
        $this->settings->refresh();
        $this->assertNull($this->settings->logo_path);
        Storage::disk('public')->assertMissing($oldPath);

        // Check fallback default tab icon is rendered
        $publicResponse = $this->get(route('home'));
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('default-logo.svg');
    }

    public function test_invalid_logo_file_type_is_rejected(): void
    {
        Storage::fake('public');

        $fakeDoc = UploadedFile::fake()->create('not-an-image.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Paddle Field Sports Center',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'logo' => $fakeDoc,
        ]);

        $response->assertSessionHasErrors('logo');
    }

    public function test_unauthorized_user_cannot_update_venue_logo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('hacker-logo.png', 100, 100);

        $response = $this->actingAs($this->client)->put(route('owner.settings.update'), [
            'venue_name' => 'Compromised Venue',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'logo' => $file,
        ]);

        $response->assertStatus(403);
    }
}
