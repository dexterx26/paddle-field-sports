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
}
