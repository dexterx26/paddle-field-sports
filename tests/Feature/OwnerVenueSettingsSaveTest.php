<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerVenueSettingsSaveTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected VenueSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->owner = User::where('role', 'court_owner')->first();
        $this->settings = VenueSetting::getSettings();
    }

    public function test_owner_can_save_all_settings_with_full_details(): void
    {
        $response = $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            '_method' => 'PUT',
            'venue_name' => 'Paddle Field Sports Center Pro',
            'tagline' => 'Premier Indoor & Outdoor Pickleball Facility',
            'opening_time' => '07:00',
            'closing_time' => '23:00',
            'phone' => '+63 917 111 2222',
            'email' => 'owner@paddlefield.com',
            'address' => '123 Pickleball Lane, Nueva Ecija',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'paymongo',
            'holding_duration_seconds' => 180,
            'credit_conversion_rate' => 200,
            'credits_enabled' => 1,
            'paymongo_simulation_mode' => 1,
        ]);

        $response->assertRedirect(route('owner.settings'));
        $response->assertSessionHas('success');

        $this->settings->refresh();
        $this->assertEquals('Paddle Field Sports Center Pro', $this->settings->venue_name);
        $this->assertEquals('Premier Indoor & Outdoor Pickleball Facility', $this->settings->tagline);
        $this->assertEquals('07:00', $this->settings->opening_time);
        $this->assertEquals('23:00', $this->settings->closing_time);
        $this->assertEquals('paymongo', $this->settings->payment_mode);
        $this->assertEquals(180, $this->settings->holding_duration_seconds);
        $this->assertEquals(200.00, (float) $this->settings->credit_conversion_rate);
        $this->assertTrue($this->settings->credits_enabled);
    }

    public function test_owner_can_save_settings_with_empty_tagline(): void
    {
        $response = $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Paddle Field Center',
            'tagline' => '', // Empty tagline should NOT cause 500 server error
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'credit_conversion_rate' => 150,
            'credits_enabled' => 0,
        ]);

        $response->assertRedirect(route('owner.settings'));
        $response->assertSessionHas('success');

        $this->settings->refresh();
        $this->assertNull($this->settings->tagline);
        $this->assertFalse($this->settings->credits_enabled);
    }

    public function test_owner_can_switch_payment_modes_and_toggle_credits(): void
    {
        // 1. Switch to Xendit with credits disabled
        $response1 = $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Paddle Field',
            'tagline' => 'Best in Town',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'xendit',
            'holding_duration_seconds' => 60,
            'credit_conversion_rate' => 100,
            'credits_enabled' => 0,
            'xendit_simulation_mode' => 1,
        ]);

        $response1->assertRedirect(route('owner.settings'));
        $this->settings->refresh();
        $this->assertEquals('xendit', $this->settings->payment_mode);
        $this->assertEquals(60, $this->settings->holding_duration_seconds);
        $this->assertFalse($this->settings->credits_enabled);

        // 2. Switch back to manual_receipt with credits enabled
        $response2 = $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Paddle Field',
            'tagline' => 'Best in Town',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'credit_conversion_rate' => 150,
            'credits_enabled' => 1,
            'manual_bank_name' => 'GCash',
            'manual_account_name' => 'Paddle Field Inc',
            'manual_account_number' => '0917-123-4567',
        ]);

        $response2->assertRedirect(route('owner.settings'));
        $this->settings->refresh();
        $this->assertEquals('manual_receipt', $this->settings->payment_mode);
        $this->assertTrue($this->settings->credits_enabled);
        $this->assertEquals('GCash', $this->settings->manual_bank_name);
    }

    public function test_validation_errors_preserve_user_inputs_via_old_helper_and_render_error_feedback(): void
    {
        // Attempt to submit with invalid email and invalid holding duration
        $response = $this->actingAs($this->owner)
            ->from(route('owner.settings'))
            ->put(route('owner.settings.update'), [
                'venue_name' => 'Attempted Venue Name Update',
                'tagline' => 'My New Tagline',
                'email' => 'invalid-not-an-email',
                'holding_duration_seconds' => 10, // Invalid: min is 30
                'opening_time' => '06:00',
                'closing_time' => '00:00',
                'currency' => 'PHP',
                'currency_symbol' => '₱',
                'payment_mode' => 'manual_receipt',
            ]);

        // Expect redirect back with validation errors and withInput
        $response->assertRedirect(route('owner.settings'));
        $response->assertSessionHasErrors(['email', 'holding_duration_seconds']);

        // Now follow redirect to settings page and ensure old inputs are rendered instead of wiping back to DB
        $pageResponse = $this->actingAs($this->owner)->get(route('owner.settings'));
        $pageResponse->assertOk();
        $pageResponse->assertSee('Attempted Venue Name Update');
        $pageResponse->assertSee('My New Tagline');
        $pageResponse->assertSee('Some settings could not be saved');
        $pageResponse->assertSee('Settings Not Saved');
    }

    public function test_owner_can_update_description_and_decimal_credit_rate(): void
    {
        $response = $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Paddle Field Sports Center',
            'tagline' => 'Premier Indoor Facility',
            'description' => 'Newly upgraded state-of-the-art acrylic courts with player lounge.',
            'opening_time' => '08:00',
            'closing_time' => '22:00',
            'phone' => '+63 917 888 9999',
            'email' => 'contact@paddlefield.com',
            'address' => 'Highway 1, Nueva Ecija',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'credit_conversion_rate' => 175.50,
            'credits_enabled' => 1,
        ]);

        $response->assertRedirect(route('owner.settings'));
        $response->assertSessionHas('success');

        $this->settings->refresh();
        $this->assertEquals('Newly upgraded state-of-the-art acrylic courts with player lounge.', $this->settings->description);
        $this->assertEquals(175.50, (float) $this->settings->credit_conversion_rate);
        $this->assertEquals('+63 917 888 9999', $this->settings->phone);
    }

    public function test_owner_can_clear_optional_fields(): void
    {
        $response = $this->actingAs($this->owner)->put(route('owner.settings.update'), [
            'venue_name' => 'Paddle Field Sports Center',
            'tagline' => '',
            'description' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'manual_bank_name' => '',
            'manual_account_name' => '',
            'manual_account_number' => '',
            'manual_payment_instructions' => '',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt',
            'holding_duration_seconds' => 120,
            'credit_conversion_rate' => 150,
            'credits_enabled' => 0,
        ]);

        $response->assertRedirect(route('owner.settings'));
        $response->assertSessionHas('success');

        $this->settings->refresh();
        $this->assertNull($this->settings->tagline);
        $this->assertNull($this->settings->description);
        $this->assertNull($this->settings->phone);
        $this->assertNull($this->settings->email);
        $this->assertNull($this->settings->address);
        $this->assertNull($this->settings->manual_bank_name);
    }
}
