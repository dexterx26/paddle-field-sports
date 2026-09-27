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
     * Test application timezone default is Asia/Manila (Philippine Time)
     */
    public function test_application_timezone_is_philippine_time(): void
    {
        $this->assertEquals('Asia/Manila', config('app.timezone'));
        $this->assertEquals('Asia/Manila', date_default_timezone_get());
        $this->assertEquals('Asia/Manila', now()->tzName);
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
     * Test PayMongo dynamic QR generation includes expiry_seconds 120
     */
    public function test_paymongo_dynamic_qr_generation_has_expiry_seconds_120(): void
    {
        $court = Court::first();
        $date = date('Y-m-d', strtotime('+1 day'));

        $holdResponse = $this->postJson('/api/hold-slots', [
            'court_id' => $court->id,
            'date' => $date,
            'slots' => ['14:00'],
            'customer_name' => 'PayMongo Tester',
            'customer_phone' => '0917-888-9999',
            'customer_email' => 'pmtester@example.com',
        ]);

        $holdResponse->assertStatus(200);
        $holdData = $holdResponse->json();
        $this->assertTrue($holdData['success']);
        $reference = $holdData['reference'];

        $booking = Booking::where('booking_reference', $reference)->first();
        $this->assertNotNull($booking);

        $paymongoService = app(\App\Services\PayMongoService::class);
        $qrResult = $paymongoService->generateDynamicQr($booking, 120);

        $this->assertTrue($qrResult['success']);
        $this->assertEquals(120, $qrResult['expiry_seconds']);
        $this->assertEquals('dynamic', $qrResult['type']);
        $this->assertNotNull($qrResult['expires_at']);

        // Also test API route endpoint
        $apiQrResponse = $this->postJson("/api/paymongo/dynamic-qr/{$reference}");
        $apiQrResponse->assertStatus(200);
        $apiQrData = $apiQrResponse->json();
        $this->assertTrue($apiQrData['success']);
        $this->assertEquals(120, $apiQrData['expiry_seconds']);
        $this->assertEquals('dynamic', $apiQrData['type']);
    }

    /**
     * Test multiple registered players exist (including Paolo Soriano) and can authenticate
     */
    public function test_multiple_registered_players_exist_and_can_authenticate(): void
    {
        $player1 = \App\Models\User::where('email', 'player@gmail.com')->first();
        $player2 = \App\Models\User::where('email', 'player2@gmail.com')->first();
        $playerPaolo = \App\Models\User::where('email', 'paolo@gmail.com')->first();

        $this->assertNotNull($player1, 'Player 1 should exist');
        $this->assertEquals('client', $player1->role);

        $this->assertNotNull($player2, 'Player 2 (Elena Cruz) should exist');
        $this->assertEquals('client', $player2->role);
        $this->assertEquals('Elena Cruz (Player 2)', $player2->name);

        $this->assertNotNull($playerPaolo, 'Player Paolo Soriano should exist');
        $this->assertEquals('client', $playerPaolo->role);
        $this->assertEquals('Paolo Soriano', $playerPaolo->name);

        // Test login page displays players in demo login
        $loginPage = $this->get('/login');
        $loginPage->assertStatus(200);
        $loginPage->assertSee('Player 1');
        $loginPage->assertSee('Player 2');
        $loginPage->assertSee('Paolo (P3)');

        // Test quick login for Paolo Soriano
        $quickResponse = $this->get('/quick-login/paolo');
        $quickResponse->assertRedirect('/');
        $this->assertAuthenticatedAs($playerPaolo);
    }

    /**
     * Test option to view calendar on client/player side while default booking stays the same
     */
    public function test_client_has_option_to_view_calendar_and_default_booking_stays_same(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify Calendar View button option is present on client side
        $response->assertSee('openCalendarModalBtn');
        $response->assertSee('Calendar View');

        // Verify Calendar Modal markup is present
        $response->assertSee('playerCalendarModal');
        $response->assertSee('Court Reservation Calendar');
        $response->assertSee('calDaysGrid');

        // Verify default booking view is intact and primary
        $response->assertSee('Court Reservation Schedule');
        $response->assertSee('courtsScheduleContainer');
        $response->assertSee('selectedDateInput');
        $response->assertSee('Today');
        $response->assertSee('Tomorrow');
    }

    /**
     * Test slot held error alert and unselection logic
     */
    public function test_slot_held_alert_and_unselection_logic(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Verify SweetAlert2 with 'Slot Held by Another Customer' is wired
        $response->assertSee('Slot Held by Another Customer');

        // Verify resetSelection is called to unselect all timeslots
        $response->assertSee('resetSelection();');
    }

    /**
     * Test checkout holding duration defaults to 120 seconds (2 mins) and is editable by court owner
     */
    public function test_court_owner_can_edit_checkout_holding_duration(): void
    {
        $settings = VenueSetting::getSettings();
        // Default is 120 seconds (2 mins)
        $this->assertEquals(120, $settings->holding_duration_seconds);

        $owner = \App\Models\User::where('role', 'court_owner')->first();
        $this->assertNotNull($owner);

        // Owner edits holding duration to 180 seconds (3 mins)
        $response = $this->actingAs($owner)->put('/owner/settings', [
            'venue_name' => $settings->venue_name,
            'tagline' => $settings->tagline,
            'description' => $settings->description,
            'phone' => $settings->phone,
            'email' => $settings->email,
            'address' => $settings->address,
            'opening_time' => $settings->opening_time,
            'closing_time' => $settings->closing_time,
            'currency' => $settings->currency,
            'currency_symbol' => $settings->currency_symbol,
            'payment_mode' => 'paymongo',
            'holding_duration_seconds' => 180, // Updated to 3 mins
            'paymongo_simulation_mode' => true,
        ]);

        $response->assertRedirect('/owner/settings');
        $settings->refresh();
        $this->assertEquals(180, $settings->holding_duration_seconds);

        // Check homepage dynamically renders 3-minute hold
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('3-Min Hold', false);
        $homeResponse->assertSee('Hold 3 Mins & Pay with PayMongo', false);
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

    /**
     * Test guest/player sees Print Pass, Facility Reminders, and Select a New Slot
     */
    public function test_guest_sees_print_pass_facility_reminders_and_select_slot_on_tracker(): void
    {
        $court = Court::first();

        // 1. Confirmed booking
        $confirmedBooking = Booking::create([
            'booking_reference' => 'PF-GUESTCONF',
            'court_id' => $court->id,
            'customer_name' => 'Guest Player',
            'customer_phone' => '09191112233',
            'booking_date' => date('Y-m-d', strtotime('+3 days')),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        $response = $this->get(route('booking.track', 'PF-GUESTCONF'));
        $response->assertStatus(200);
        $response->assertSee('Print Pass');
        $response->assertSee('Facility Reminders:');

        // 2. Expired booking
        $expiredBooking = Booking::create([
            'booking_reference' => 'PF-GUESTEXP',
            'court_id' => $court->id,
            'customer_name' => 'Expired Guest',
            'customer_phone' => '09191112233',
            'booking_date' => date('Y-m-d', strtotime('+3 days')),
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'expired',
        ]);

        $responseExpired = $this->get(route('booking.track', 'PF-GUESTEXP'));
        $responseExpired->assertStatus(200);
        $responseExpired->assertSee('Select a New Slot');
        $responseExpired->assertSee('Print Pass');
        $responseExpired->assertSee('Facility Reminders:');
    }

    /**
     * Test pickle ball court owner hides Print Pass, Facility Reminders, and Select a New Slot
     */
    public function test_owner_does_not_see_print_pass_reminders_or_select_slot_on_tracker(): void
    {
        $court = Court::first();
        $owner = \App\Models\User::where('role', 'court_owner')->first();

        // 1. Confirmed booking viewed by court owner
        $confirmedBooking = Booking::create([
            'booking_reference' => 'PF-OWNERCONF',
            'court_id' => $court->id,
            'customer_name' => 'Valued Player',
            'customer_phone' => '09191112233',
            'booking_date' => date('Y-m-d', strtotime('+3 days')),
            'start_time' => '14:00:00',
            'end_time' => '15:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs($owner)->get(route('booking.track', 'PF-OWNERCONF'));
        $response->assertStatus(200);
        $response->assertDontSee('Print Pass');
        $response->assertDontSee('Facility Reminders:');
        $response->assertSee('Owner View');
        $response->assertSee('Back to Reservations Management');

        // 2. Expired booking viewed by court owner
        $expiredBooking = Booking::create([
            'booking_reference' => 'PF-OWNEREXP',
            'court_id' => $court->id,
            'customer_name' => 'Expired Player',
            'customer_phone' => '09191112233',
            'booking_date' => date('Y-m-d', strtotime('+3 days')),
            'start_time' => '16:00:00',
            'end_time' => '17:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'expired',
        ]);

        $responseExpired = $this->actingAs($owner)->get(route('booking.track', 'PF-OWNEREXP'));
        $responseExpired->assertStatus(200);
        $responseExpired->assertDontSee('Select a New Slot');
        $responseExpired->assertDontSee('Print Pass');
        $responseExpired->assertDontSee('Facility Reminders:');

        // 3. Admin also enjoys the owner view
        $admin = \App\Models\User::where('role', 'admin')->first();
        $responseAdmin = $this->actingAs($admin)->get(route('booking.track', 'PF-OWNERCONF'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertDontSee('Print Pass');
        $responseAdmin->assertDontSee('Facility Reminders:');
    }

    /**
     * Test owner/bookings default view only shows confirmed bookings, but allows all or specific statuses
     */
    public function test_owner_bookings_default_view_only_shows_confirmed(): void
    {
        $court = Court::first();
        $owner = \App\Models\User::where('role', 'court_owner')->first();

        // Create confirmed booking
        Booking::create([
            'booking_reference' => 'PF-DEFAULTCONF',
            'court_id' => $court->id,
            'customer_name' => 'Confirmed Player Alpha',
            'customer_phone' => '09191110001',
            'booking_date' => date('Y-m-d', strtotime('+1 day')),
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // Create pending_approval booking
        Booking::create([
            'booking_reference' => 'PF-DEFAULTPEND',
            'court_id' => $court->id,
            'customer_name' => 'Pending Player Beta',
            'customer_phone' => '09191110002',
            'booking_date' => date('Y-m-d', strtotime('+1 day')),
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'unpaid',
            'booking_status' => 'pending_approval',
        ]);

        // Create expired booking
        Booking::create([
            'booking_reference' => 'PF-DEFAULTEXP',
            'court_id' => $court->id,
            'customer_name' => 'Expired Player Gamma',
            'customer_phone' => '09191110003',
            'booking_date' => date('Y-m-d', strtotime('+1 day')),
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'expired',
        ]);

        // 1. Default visit to owner/bookings: MUST ONLY show confirmed
        $response = $this->actingAs($owner)->get(route('owner.bookings.index'));
        $response->assertStatus(200);
        $response->assertViewHas('status', 'confirmed');
        $response->assertSee('PF-DEFAULTCONF');
        $response->assertDontSee('PF-DEFAULTPEND');
        $response->assertDontSee('PF-DEFAULTEXP');

        // 2. Filter by status=all: MUST show all statuses
        $responseAll = $this->actingAs($owner)->get(route('owner.bookings.index', ['status' => 'all']));
        $responseAll->assertStatus(200);
        $responseAll->assertViewHas('status', 'all');
        $responseAll->assertSee('PF-DEFAULTCONF');
        $responseAll->assertSee('PF-DEFAULTPEND');
        $responseAll->assertSee('PF-DEFAULTEXP');

        // 3. Filter by status=pending_approval: MUST show only pending
        $responsePending = $this->actingAs($owner)->get(route('owner.bookings.index', ['status' => 'pending_approval']));
        $responsePending->assertStatus(200);
        $responsePending->assertViewHas('status', 'pending_approval');
        $responsePending->assertDontSee('PF-DEFAULTCONF');
        $responsePending->assertSee('PF-DEFAULTPEND');
        $responsePending->assertDontSee('PF-DEFAULTEXP');

        // 4. Search with status=all
        $responseSearchAll = $this->actingAs($owner)->get(route('owner.bookings.index', [
            'search' => 'Gamma',
            'status' => 'all',
        ]));
        $responseSearchAll->assertStatus(200);
        $responseSearchAll->assertSee('PF-DEFAULTEXP');
        $responseSearchAll->assertDontSee('PF-DEFAULTCONF');
    }

    /**
     * Test Today's Court Reservations table on owner dashboard has modal and details button
     */
    public function test_owner_dashboard_today_reservations_has_details_modal_button(): void
    {
        $court = Court::first();
        $owner = \App\Models\User::where('role', 'court_owner')->first();

        // Create a booking for today
        $todayBooking = Booking::create([
            'booking_reference' => 'PF-TODAY-001',
            'court_id' => $court->id,
            'customer_name' => 'Court Master Player',
            'customer_phone' => '09170009999',
            'customer_email' => 'courtmaster@example.com',
            'booking_date' => date('Y-m-d'),
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'total_hours' => 2,
            'rate_per_hour' => 150.00,
            'total_amount' => 300.00,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        \App\Models\BookingSlot::create([
            'booking_id' => $todayBooking->id,
            'court_id' => $court->id,
            'date' => date('Y-m-d'),
            'slot_time' => '15:00:00',
            'rate_per_hour' => 150.00,
            'status' => 'confirmed',
        ]);

        \App\Models\BookingSlot::create([
            'booking_id' => $todayBooking->id,
            'court_id' => $court->id,
            'date' => date('Y-m-d'),
            'slot_time' => '16:00:00',
            'rate_per_hour' => 150.00,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($owner)->get(route('owner.dashboard'));
        $response->assertStatus(200);
        $response->assertSee("Today's Court Reservations", false);
        $response->assertSee('PF-TODAY-001');
        $response->assertSee('Court Master Player');
        $response->assertSee('Details');
        $response->assertSee('id="reservationDetailsModal"', false);
        $response->assertSee('resModalRef', false);
        $response->assertSee('resModalSlotsPills', false);
        $response->assertSee('openReservationModal', false);
    }

    /**
     * Test Today's Court Reservations table has court filter, sorts time descending, and puts past times at bottom
     */
    public function test_owner_dashboard_today_court_filter_and_descending_past_at_bottom(): void
    {
        $owner = \App\Models\User::where('role', 'court_owner')->first();
        $courts = Court::where('is_active', true)->orderBy('court_number')->get();
        $court1 = $courts[0];
        $court2 = $courts[1];

        $now = \Carbon\Carbon::today()->setTime(12, 0, 0);
        \Carbon\Carbon::setTestNow($now);
        $today = $now->format('Y-m-d');
        \App\Models\BookingSlot::whereDate('date', $today)->delete();
        Booking::whereDate('booking_date', $today)->delete();

        // Past booking today: 06:00 - 07:00 (Court 1)
        $pastBooking = Booking::create([
            'booking_reference' => 'PF-PAST-0600',
            'court_id' => $court1->id,
            'customer_name' => 'Early Morning Player',
            'customer_phone' => '09171110001',
            'booking_date' => $today,
            'start_time' => '06:00:00',
            'end_time' => '07:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // Upcoming booking today: 19:00 - 20:00 (Court 2)
        $upcomingBooking1 = Booking::create([
            'booking_reference' => 'PF-UPCOMING-1900',
            'court_id' => $court2->id,
            'customer_name' => 'Evening Player',
            'customer_phone' => '09171110002',
            'booking_date' => $today,
            'start_time' => '19:00:00',
            'end_time' => '20:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // Upcoming later booking today: 22:00 - 23:00 (Court 1)
        $upcomingBooking2 = Booking::create([
            'booking_reference' => 'PF-UPCOMING-2200',
            'court_id' => $court1->id,
            'customer_name' => 'Late Night Player',
            'customer_phone' => '09171110003',
            'booking_date' => $today,
            'start_time' => '22:00:00',
            'end_time' => '23:00:00',
            'total_hours' => 1,
            'rate_per_hour' => 150.00,
            'total_amount' => 150.00,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // 1. Visit dashboard: verify court filter pills and dropdown are rendered
        $response = $this->actingAs($owner)->get(route('owner.dashboard'));
        $response->assertStatus(200);

        // Filter elements present
        $response->assertSee('id="todayCourtFilter"', false);
        $response->assertSee('data-court-filter="all"', false);
        $response->assertSee('data-court-filter="' . $court1->id . '"', false);
        $response->assertSee('data-court-filter="' . $court2->id . '"', false);

        // Verify sorting:
        // $todayBookings in view:
        // Index 0: PF-UPCOMING-2200 (latest upcoming, non-past)
        // Index 1: PF-UPCOMING-1900 (earlier upcoming, non-past)
        // Index 2: PF-PAST-0600 (past time today, placed at the bottom!)
        $todayBookings = $response->viewData('todayBookings');
        $this->assertCount(3, $todayBookings);

        $references = $todayBookings->pluck('booking_reference')->values()->all();
        $this->assertEquals('PF-UPCOMING-2200', $references[0]);
        $this->assertEquals('PF-UPCOMING-1900', $references[1]);
        $this->assertEquals('PF-PAST-0600', $references[2]);

        $this->assertFalse($todayBookings[0]->is_past);
        $this->assertFalse($todayBookings[1]->is_past);
        $this->assertTrue($todayBookings[2]->is_past);

        // Verify HTML order: 22:00 comes before 19:00, which comes before 06:00
        $content = $response->getContent();
        $pos2200 = strpos($content, 'PF-UPCOMING-2200');
        $pos1900 = strpos($content, 'PF-UPCOMING-1900');
        $pos0600 = strpos($content, 'PF-PAST-0600');

        $this->assertTrue($pos2200 < $pos1900, 'PF-UPCOMING-2200 should appear before PF-UPCOMING-1900');
        $this->assertTrue($pos1900 < $pos0600, 'PF-UPCOMING-1900 should appear before past PF-PAST-0600');

        // Past divider is shown
        $response->assertSee('Past Time Today');

        // 2. Visit dashboard with court_id filter for Court 2:
        $responseCourt2 = $this->actingAs($owner)->get(route('owner.dashboard', ['court_id' => $court2->id]));
        $responseCourt2->assertStatus(200);
        $responseCourt2->assertViewHas('selectedCourtId', (string) $court2->id);

        $htmlCourt2 = $responseCourt2->getContent();
        // Court 1 cards are hidden by class
        $this->assertMatchesRegularExpression('/today-booking-card[^>]*hidden[^>]*data-court-id="' . $court1->id . '"/', $htmlCourt2);
        // Court 2 card is NOT hidden by class
        $this->assertDoesNotMatchRegularExpression('/today-booking-card[^>]*hidden[^>]*data-court-id="' . $court2->id . '"/', $htmlCourt2);

        \Carbon\Carbon::setTestNow();
    }

    /**
     * Test holding time synchronization across players and auto-release when expired
     */
    public function test_holding_time_synchronization_and_auto_release_when_expired(): void
    {
        $settings = VenueSetting::getSettings();
        // Set holding time to 60 seconds (1 minute)
        $settings->update(['holding_duration_seconds' => 60]);

        $court = Court::first();
        $date = date('Y-m-d', strtotime('+3 days'));
        $slotTime = '16:00';

        // 1. Customer 1 holds the slot
        $holdRes = $this->postJson('/api/hold-slots', [
            'court_id' => $court->id,
            'date' => $date,
            'slots' => [$slotTime],
            'customer_name' => 'Alice Customer',
            'customer_phone' => '09171112222',
        ]);

        $holdRes->assertStatus(200);
        $holdData = $holdRes->json();
        $this->assertTrue($holdData['success']);
        $this->assertEquals(60, $holdData['remaining_seconds'], 'Hold remaining seconds should match owner setting of 60s');
        $reference = $holdData['reference'];

        // 2. Other players view availability: remaining seconds should be <= 60 and status 'held'
        $availRes = $this->getJson("/api/availability?date={$date}");
        $availRes->assertStatus(200);
        $courtAvail = collect($availRes->json('courts'))->firstWhere('court.id', $court->id);
        $this->assertEquals('held', $courtAvail['slots'][$slotTime]['status']);
        $this->assertLessThanOrEqual(60, $courtAvail['slots'][$slotTime]['remaining_seconds']);
        $this->assertGreaterThan(0, $courtAvail['slots'][$slotTime]['remaining_seconds']);

        // 3. Fast-forward time past 60s to simulate customer letting the hold expire
        \Carbon\Carbon::setTestNow(now()->addSeconds(65));

        // When availability is fetched by another player, expired hold must be released automatically
        $expiredAvailRes = $this->getJson("/api/availability?date={$date}");
        $expiredAvailRes->assertStatus(200);
        $courtAvailAfter = collect($expiredAvailRes->json('courts'))->firstWhere('court.id', $court->id);
        $this->assertEquals('available', $courtAvailAfter['slots'][$slotTime]['status'], 'Expired slot should automatically be released to available');

        // Customer 2 can now successfully hold the released slot
        $cust2HoldRes = $this->postJson('/api/hold-slots', [
            'court_id' => $court->id,
            'date' => $date,
            'slots' => [$slotTime],
            'customer_name' => 'Bob Customer',
            'customer_phone' => '09183334444',
        ]);
        $cust2HoldRes->assertStatus(200);
        $this->assertTrue($cust2HoldRes->json('success'));

        // Reset
        \Carbon\Carbon::setTestNow();
        $settings->update(['holding_duration_seconds' => 120]);
    }
}

