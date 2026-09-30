<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CourtSettingsAndOwnerReservationTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $regularUser;
    protected BookingService $bookingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->owner = User::where('role', 'court_owner')->first()
            ?? User::factory()->create(['role' => 'court_owner']);

        $this->regularUser = User::where('role', 'user')->first()
            ?? User::factory()->create(['role' => 'user']);

        $this->bookingService = app(BookingService::class);
    }

    /**
     * Requirement 1: Court operating hours can be updated by court owner,
     * and slot generation conforms strictly to court operating hours.
     */
    public function test_owner_can_update_court_operating_hours(): void
    {
        $court = Court::first();

        $response = $this->actingAs($this->owner)
            ->put(route('owner.courts.update', $court->id), [
                'name' => $court->name,
                'court_number' => $court->court_number,
                'type' => $court->type,
                'surface_type' => $court->surface_type,
                'price_per_hour' => 180,
                'max_players' => 4,
                'description' => 'Updated court description',
                'is_active' => '1',
                'opening_time' => '08:00',
                'closing_time' => '17:00',
            ]);

        $response->assertRedirect(route('owner.courts.index'));
        $response->assertSessionHas('success');

        $court->refresh();
        $this->assertEquals('08:00', $court->opening_time);
        $this->assertEquals('17:00', $court->closing_time);
        $this->assertEquals(8, $court->start_hour);
        $this->assertEquals(17, $court->end_hour);
        $this->assertEquals(9, $court->total_operating_hours);
        $this->assertEquals('8:00 AM - 5:00 PM', $court->operating_hours_label);
    }

    /**
     * Requirement 1: getAvailability only produces slots within court operating hours.
     */
    public function test_availability_reflects_court_specific_operating_hours(): void
    {
        $court = Court::first();
        // Set court from 6:00 AM to 5:00 PM (17:00)
        $court->update([
            'opening_time' => '06:00',
            'closing_time' => '17:00',
        ]);

        $testDate = date('Y-m-d', strtotime('+3 days'));
        $availability = $this->bookingService->getAvailability($testDate);

        $courtData = collect($availability)->firstWhere('court.id', $court->id);
        $this->assertNotNull($courtData);

        $courtSlots = $courtData['slots'] ?? [];
        $this->assertNotEmpty($courtSlots);

        // First slot should be 06:00, last slot should be 16:00 (since 16:00 - 17:00 is the final 1-hour slot)
        $times = array_keys($courtSlots);
        $this->assertEquals('06:00', $times[0]);
        $this->assertEquals('16:00', end($times));
        $this->assertCount(11, $times); // 6,7,8,9,10,11,12,13,14,15,16 => 11 slots

        // Check slots beyond 17:00 are not generated for this court
        $this->assertArrayNotHasKey('17:00', $courtSlots);
        $this->assertArrayNotHasKey('18:00', $courtSlots);
        $this->assertArrayNotHasKey('23:00', $courtSlots);
    }

    /**
     * Requirement 1: areSlotsAvailable rejects times outside court operating hours.
     */
    public function test_slots_outside_court_operating_hours_are_rejected(): void
    {
        $court = Court::first();
        $court->update([
            'opening_time' => '06:00',
            'closing_time' => '17:00', // Closes at 5 PM
        ]);

        $testDate = date('Y-m-d', strtotime('+3 days'));

        // Slot inside hours: should be available
        $this->assertTrue(
            $this->bookingService->areSlotsAvailable($court->id, $testDate, ['08:00', '09:00'])
        );

        // Slot outside hours (18:00): must be rejected
        $this->assertFalse(
            $this->bookingService->areSlotsAvailable($court->id, $testDate, ['18:00'])
        );
    }

    /**
     * Requirement 2: Court owner can manually reserve whole court for offline rental.
     */
    public function test_owner_can_manually_reserve_whole_court(): void
    {
        $court = Court::first();
        $court->update([
            'opening_time' => '06:00',
            'closing_time' => '17:00',
        ]);

        $testDate = date('Y-m-d', strtotime('+5 days'));

        $response = $this->actingAs($this->owner)
            ->from(route('owner.bookings.index'))
            ->post(route('owner.bookings.reserve'), [
                'court_id' => $court->id,
                'date' => $testDate,
                'slot_mode' => 'all_day',
                'customer_name' => 'Corporate Pickleball Tournament',
                'customer_phone' => '09171234567',
                'customer_email' => 'events@corporate.com',
                'players_count' => 12,
                'total_amount' => 2000,
                'payment_status' => 'paid',
                'notes' => 'Whole court tournament offline reservation',
            ]);

        $response->assertRedirect(route('owner.bookings.index'));
        $response->assertSessionHas('success');

        $booking = Booking::where('customer_name', 'Corporate Pickleball Tournament')->first();
        $this->assertNotNull($booking);
        $this->assertEquals($court->id, $booking->court_id);
        $this->assertEquals('confirmed', $booking->booking_status);
        $this->assertEquals('paid', $booking->payment_status);
        $this->assertEquals('walk_in_offline', $booking->payment_method);
        $this->assertEquals(2000.00, (float) $booking->total_amount);
        $this->assertEquals('06:00', $booking->start_time);
        $this->assertEquals('17:00', $booking->end_time);

        // Check booking slots: 11 slots created and all confirmed
        $slots = BookingSlot::where('booking_id', $booking->id)->get();
        $this->assertCount(11, $slots);
        foreach ($slots as $slot) {
            $this->assertEquals('confirmed', $slot->status);
        }

        // Slots should now be completely booked in availability
        $availability = $this->bookingService->getAvailability($testDate);
        $courtData = collect($availability)->firstWhere('court.id', $court->id);
        foreach ($courtData['slots'] as $time => $slotInfo) {
            $this->assertEquals('confirmed', $slotInfo['status']);
        }
    }

    /**
     * Requirement 2: Court owner can manually reserve specific slots.
     */
    public function test_owner_can_manually_reserve_custom_slots(): void
    {
        $court = Court::first();
        $court->update([
            'opening_time' => '06:00',
            'closing_time' => '22:00',
        ]);

        $testDate = date('Y-m-d', strtotime('+6 days'));

        $response = $this->actingAs($this->owner)
            ->from(route('owner.bookings.index'))
            ->post(route('owner.bookings.reserve'), [
                'court_id' => $court->id,
                'date' => $testDate,
                'slot_mode' => 'custom',
                'slots' => ['14:00', '15:00'],
                'customer_name' => 'Coach Alex Private Clinic',
                'customer_phone' => '09228889999',
                'total_amount' => 500,
                'payment_status' => 'unpaid',
                'notes' => 'Cash on arrival',
            ]);

        $response->assertRedirect(route('owner.bookings.index'));
        $response->assertSessionHas('success');

        $booking = Booking::where('customer_name', 'Coach Alex Private Clinic')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('confirmed', $booking->booking_status);
        $this->assertEquals('unpaid', $booking->payment_status);
        $this->assertEquals('14:00', $booking->start_time);
        $this->assertEquals('16:00', $booking->end_time);
        $this->assertEquals(2, $booking->total_hours);

        $slots = BookingSlot::where('booking_id', $booking->id)->pluck('slot_time')->all();
        $this->assertEquals(['14:00', '15:00'], $slots);
    }

    /**
     * Requirement 3: Court owner can cancel any user's reservation and release slots.
     */
    public function test_owner_can_cancel_user_reservation_and_release_slots(): void
    {
        $court = Court::first();
        $testDate = date('Y-m-d', strtotime('+7 days'));

        // Create confirmed booking for a regular user
        $booking = Booking::create([
            'booking_reference' => 'PF-' . date('Ymd') . '-USER01',
            'court_id' => $court->id,
            'user_id' => $this->regularUser->id,
            'customer_name' => $this->regularUser->name,
            'customer_phone' => '09123456789',
            'customer_email' => $this->regularUser->email,
            'players_count' => 4,
            'booking_date' => $testDate,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'total_hours' => 2,
            'rate_per_hour' => $court->price_per_hour,
            'total_amount' => $court->price_per_hour * 2,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        BookingSlot::create([
            'booking_id' => $booking->id,
            'court_id' => $court->id,
            'date' => $testDate,
            'slot_time' => '10:00',
            'status' => 'confirmed',
            'rate_per_hour' => $court->price_per_hour,
        ]);
        BookingSlot::create([
            'booking_id' => $booking->id,
            'court_id' => $court->id,
            'date' => $testDate,
            'slot_time' => '11:00',
            'status' => 'confirmed',
            'rate_per_hour' => $court->price_per_hour,
        ]);

        // Prior to cancellation, slots are unavailable
        $this->assertFalse(
            $this->bookingService->areSlotsAvailable($court->id, $testDate, ['10:00', '11:00'])
        );

        // Owner cancels the reservation
        $cancelReason = 'Customer called to cancel due to weather emergency';
        $response = $this->actingAs($this->owner)
            ->from(route('owner.bookings.index'))
            ->post(route('owner.bookings.cancel', $booking->id), [
                'reason' => $cancelReason,
            ]);

        $response->assertRedirect(route('owner.bookings.index'));
        $response->assertSessionHas('success');

        // Check booking status is cancelled
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->booking_status);
        $this->assertStringContainsString($cancelReason, $booking->rejection_reason);

        // Check booking slots are released
        $slots = BookingSlot::where('booking_id', $booking->id)->get();
        foreach ($slots as $slot) {
            $this->assertEquals('released', $slot->status);
        }

        // Check slots are immediately available again
        $this->assertTrue(
            $this->bookingService->areSlotsAvailable($court->id, $testDate, ['10:00', '11:00'])
        );
    }

    /**
     * Requirement 3: Non-owners cannot cancel reservations through owner route.
     */
    public function test_regular_user_cannot_cancel_booking_via_owner_endpoint(): void
    {
        $court = Court::first();
        $testDate = date('Y-m-d', strtotime('+8 days'));

        $booking = Booking::create([
            'booking_reference' => 'PF-' . date('Ymd') . '-USER02',
            'court_id' => $court->id,
            'user_id' => $this->regularUser->id,
            'customer_name' => $this->regularUser->name,
            'customer_phone' => '09123456789',
            'booking_date' => $testDate,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'total_hours' => 1,
            'rate_per_hour' => 150,
            'total_amount' => 150,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'unpaid',
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->post(route('owner.bookings.cancel', $booking->id), [
                'reason' => 'Unauthorized cancellation attempt',
            ]);

        $response->assertForbidden();

        $booking->refresh();
        $this->assertEquals('confirmed', $booking->booking_status);
    }
}
