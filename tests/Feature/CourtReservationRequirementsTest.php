<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\VenueSetting;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourtReservationRequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test venue settings defaults
     */
    public function test_venue_settings_defaults(): void
    {
        $settings = VenueSetting::getSettings();

        $this->assertEquals('Bacal 3, Talavera, Nueva Ecija', $settings->address);
        $this->assertEquals('00:00', $settings->closing_time);
        $this->assertEquals('06:00', $settings->opening_time);
    }

    /**
     * Test courts count and default rate
     */
    public function test_courts_are_three_indoor_at_150_rate(): void
    {
        $courts = Court::where('is_active', true)->get();

        $this->assertCount(3, $courts);
        foreach ($courts as $court) {
            $this->assertEquals('indoor', $court->type);
            $this->assertEquals(150.00, (float) $court->price_per_hour);
        }
    }

    /**
     * Test 18 timeslots generation up to 12 AM Midnight
     */
    public function test_availability_generates_slots_up_to_midnight(): void
    {
        $service = app(BookingService::class);
        $availability = $service->getAvailability(date('Y-m-d'));

        $this->assertNotEmpty($availability);
        $firstCourtSlots = $availability[0]['slots'];

        // From 06:00 to 24:00 is 18 hourly slots
        $this->assertCount(18, $firstCourtSlots);
        $this->assertArrayHasKey('06:00', $firstCourtSlots);
        $this->assertArrayHasKey('23:00', $firstCourtSlots);

        $lastSlot = $firstCourtSlots['23:00'];
        $this->assertEquals('11:00 PM', $lastSlot['display_time']);
        $this->assertEquals('11:00 PM - 12:00 AM', $lastSlot['display_range']);
    }

    /**
     * Test soft delete court preserves bookings, slots, and revenue calculations
     */
    public function test_soft_delete_preserves_revenue_and_slots(): void
    {
        $court = Court::first();
        $this->assertNotNull($court);

        $revBefore = (float) Booking::where('booking_status', 'confirmed')->sum('total_amount');
        $slotsBefore = BookingSlot::where('court_id', $court->id)->count();

        // Soft delete court
        $court->delete();

        // Court should be soft-deleted
        $this->assertSoftDeleted('courts', ['id' => $court->id]);

        // Revenue must remain 100% preserved
        $revAfter = (float) Booking::where('booking_status', 'confirmed')->sum('total_amount');
        $this->assertEquals($revBefore, $revAfter);

        // Booking slots must not be cascaded away
        $slotsAfter = BookingSlot::where('court_id', $court->id)->count();
        $this->assertEquals($slotsBefore, $slotsAfter);

        // Court should not appear in active public courts
        $activeCourts = Court::where('is_active', true)->get();
        $this->assertFalse($activeCourts->contains('id', $court->id));

        // Restore court
        $court->restore();
        $this->assertNull($court->deleted_at);
    }

    /**
     * Test public homepage returns 200 and has scroll target
     */
    public function test_homepage_has_light_mode_and_court_targets(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Check for court card IDs for smooth scrolling
        $court = Court::first();
        $response->assertSee('id="booking-court-' . $court->id . '"', false);
        $response->assertSee('scrollToCourtInEngine(' . $court->id . ')', false);

        // Check for Bacal 3 address
        $response->assertSee('Bacal 3, Talavera, Nueva Ecija');

        // Check for 12:00 AM Midnight
        $response->assertSee('12:00 AM Midnight');
    }

    /**
     * Test sweetalert2 inclusion and absence of native alert calls
     */
    public function test_sweetalert2_and_dynamic_slot_selection(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Check SweetAlert2 script CDN is loaded
        $response->assertSee('cdn.jsdelivr.net/npm/sweetalert2', false);

        // Check Swal.fire is used for customer alerts
        $response->assertSee('Swal.fire', false);

        // Verify no native browser alert() remains
        $response->assertDontSee('alert(', false);

        // Verify unified slot interaction function is present
        $response->assertSee('handleSlotClickOrDrag', false);
    }

    /**
     * Test PayMongo venue setting configuration
     */
    public function test_paymongo_payment_mode_configuration(): void
    {
        $settings = VenueSetting::getSettings();
        $settings->update([
            'payment_mode' => 'paymongo',
            'paymongo_secret_key' => 'sk_test_123456789',
            'paymongo_public_key' => 'pk_test_123456789',
            'paymongo_simulation_mode' => true,
        ]);

        $settings->refresh();
        $this->assertEquals('paymongo', $settings->payment_mode);
        $this->assertEquals('sk_test_123456789', $settings->paymongo_secret_key);
        $this->assertTrue($settings->paymongo_simulation_mode);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('PayMongo Auto');
        $response->assertSee('Hold 2 Mins & Pay with PayMongo', false);
    }

    /**
     * Test held timeslots cannot be selected or booked by another player
     */
    public function test_held_timeslots_cannot_be_booked_by_other_players(): void
    {
        $court = Court::first();
        $date = date('Y-m-d', strtotime('+1 day'));
        $slots = ['10:00', '11:00'];

        // Customer 1 holds the slot
        $holdResponse = $this->postJson('/api/hold-slots', [
            'court_id' => $court->id,
            'date' => $date,
            'slots' => $slots,
            'customer_name' => 'Player One',
            'customer_phone' => '09171234567',
        ]);

        $holdResponse->assertStatus(200);
        $holdData = $holdResponse->json();
        $this->assertTrue($holdData['success']);
        $reference = $holdData['reference'];

        // Verify availability reports the slots as 'held'
        $availabilityResponse = $this->getJson("/api/availability?date={$date}");
        $availabilityResponse->assertStatus(200);
        $courtAvail = collect($availabilityResponse->json('courts'))->firstWhere('court.id', $court->id);
        $this->assertNotNull($courtAvail);
        $this->assertEquals('held', $courtAvail['slots']['10:00']['status']);
        $this->assertEquals('held', $courtAvail['slots']['11:00']['status']);

        // Customer 2 attempts to hold the same slot -> MUST BE REJECTED
        $conflictResponse = $this->postJson('/api/hold-slots', [
            'court_id' => $court->id,
            'date' => $date,
            'slots' => ['10:00'],
            'customer_name' => 'Player Two',
            'customer_phone' => '09187654321',
        ]);

        $conflictResponse->assertStatus(422);
        $conflictData = $conflictResponse->json();
        $this->assertFalse($conflictData['success']);
        $this->assertStringContainsString('held', strtolower($conflictData['message']));

        // Cancel the hold
        $cancelResponse = $this->postJson("/api/cancel-hold/{$reference}");
        $cancelResponse->assertStatus(200);

        // Now slots should be available again
        $availabilityResponseAfter = $this->getJson("/api/availability?date={$date}");
        $courtAvailAfter = collect($availabilityResponseAfter->json('courts'))->firstWhere('court.id', $court->id);
        $this->assertNotNull($courtAvailAfter);
        $this->assertEquals('available', $courtAvailAfter['slots']['10:00']['status']);
    }

    /**
     * Test confirmed booking timeslots cannot be held or booked
     */
    public function test_confirmed_booking_timeslots_cannot_be_booked(): void
    {
        $court = Court::first();
        $date = date('Y-m-d', strtotime('+2 days'));
        $slotTime = '14:00';

        // Create confirmed booking
        $booking = Booking::create([
            'booking_reference' => 'PF-' . strtoupper(uniqid()),
            'court_id' => $court->id,
            'customer_name' => 'Confirmed Player',
            'customer_phone' => '09191112233',
            'booking_date' => $date,
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        BookingSlot::create([
            'booking_id' => $booking->id,
            'court_id' => $court->id,
            'date' => $date,
            'slot_time' => $slotTime,
            'rate_per_hour' => 150.00,
            'status' => 'confirmed',
        ]);

        // Verify availability reports as 'confirmed'
        $avail = $this->getJson("/api/availability?date={$date}");
        $avail->assertStatus(200);
        $courtAvail = collect($avail->json('courts'))->firstWhere('court.id', $court->id);
        $this->assertNotNull($courtAvail);
        $this->assertEquals('confirmed', $courtAvail['slots'][$slotTime]['status']);

        // Another player tries to hold the confirmed slot -> MUST FAIL
        $tryHold = $this->postJson('/api/hold-slots', [
            'court_id' => $court->id,
            'date' => $date,
            'slots' => [$slotTime],
            'customer_name' => 'Intruder Player',
            'customer_phone' => '09199998888',
        ]);

        $tryHold->assertStatus(422);
        $this->assertFalse($tryHold->json('success'));
    }
}
