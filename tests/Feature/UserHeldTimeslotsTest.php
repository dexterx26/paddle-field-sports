<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\User;
use App\Models\VenueSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserHeldTimeslotsTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $client;
    protected Court $court;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->owner = User::where('role', 'court_owner')->first();
        $this->client = User::where('role', 'client')->first();
        $this->court = Court::first();
    }

    public function test_held_timeslots_counter_accurately_counts_expired_cancelled_and_active_holds(): void
    {
        // Initially 0 held slots
        $this->assertEquals(0, $this->client->held_timeslots_count);

        // 1. Create an expired hold (2 hours = 2 timeslots)
        $expiredBooking = Booking::create([
            'booking_reference' => 'PF-TEST-EXP1',
            'user_id' => $this->client->id,
            'customer_name' => $this->client->name,
            'customer_phone' => '+63 918 000 0001',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'total_hours' => 2,
            'rate_per_hour' => 500,
            'total_amount' => 1000,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'expired',
        ]);

        BookingSlot::create([
            'booking_id' => $expiredBooking->id,
            'court_id' => $this->court->id,
            'date' => today()->toDateString(),
            'slot_time' => '10:00',
            'rate_per_hour' => 500,
            'status' => 'released',
        ]);
        BookingSlot::create([
            'booking_id' => $expiredBooking->id,
            'court_id' => $this->court->id,
            'date' => today()->toDateString(),
            'slot_time' => '11:00',
            'rate_per_hour' => 500,
            'status' => 'released',
        ]);

        // 2. Create a cancelled hold (1 hour = 1 timeslot)
        Booking::create([
            'booking_reference' => 'PF-TEST-CAN1',
            'user_id' => $this->client->id,
            'customer_name' => $this->client->name,
            'customer_phone' => '+63 918 000 0001',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '14:00',
            'end_time' => '15:00',
            'total_hours' => 1,
            'rate_per_hour' => 500,
            'total_amount' => 500,
            'payment_method' => 'xendit',
            'payment_status' => 'unpaid',
            'booking_status' => 'cancelled',
        ]);

        // 3. Create an active hold (1 hour = 1 timeslot)
        Booking::create([
            'booking_reference' => 'PF-TEST-ACT1',
            'user_id' => $this->client->id,
            'customer_name' => $this->client->name,
            'customer_phone' => '+63 918 000 0001',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '16:00',
            'end_time' => '17:00',
            'total_hours' => 1,
            'rate_per_hour' => 500,
            'total_amount' => 500,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'held',
            'held_until' => now()->addMinutes(2),
        ]);

        // Total held timeslots should be 2 + 1 + 1 = 4
        $this->assertEquals(4, $this->client->fresh()->held_timeslots_count);
        $this->assertEquals(1, $this->client->fresh()->active_held_slots_count);
        $this->assertEquals(3, $this->client->fresh()->held_bookings_count);
    }

    public function test_confirmed_paid_bookings_and_manual_receipts_are_not_counted_as_held_slots(): void
    {
        // 1. Confirmed paid booking
        Booking::create([
            'booking_reference' => 'PF-TEST-CONF',
            'user_id' => $this->client->id,
            'customer_name' => $this->client->name,
            'customer_phone' => '+63 918 000 0001',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:00',
            'total_hours' => 2,
            'rate_per_hour' => 500,
            'total_amount' => 1000,
            'payment_method' => 'paymongo',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
        ]);

        // 2. Manual receipt pending approval booking
        Booking::create([
            'booking_reference' => 'PF-TEST-PEND',
            'user_id' => $this->client->id,
            'customer_name' => $this->client->name,
            'customer_phone' => '+63 918 000 0001',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '12:00',
            'end_time' => '13:00',
            'total_hours' => 1,
            'rate_per_hour' => 500,
            'total_amount' => 500,
            'payment_method' => 'manual_receipt',
            'payment_status' => 'unpaid',
            'booking_status' => 'pending_approval',
        ]);

        // Held timeslots must still be 0 because neither was an abandoned/unpaid held slot
        $this->assertEquals(0, $this->client->fresh()->held_timeslots_count);
        $this->assertEquals(0, $this->client->fresh()->active_held_slots_count);
    }

    public function test_held_timeslots_counter_displayed_in_user_management(): void
    {
        // Create an expired held slot for the client
        Booking::create([
            'booking_reference' => 'PF-TEST-EXP2',
            'user_id' => $this->client->id,
            'customer_name' => $this->client->name,
            'customer_phone' => '+63 918 000 0001',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '18:00',
            'end_time' => '21:00',
            'total_hours' => 3,
            'rate_per_hour' => 500,
            'total_amount' => 1500,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'expired',
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.users.index'));
        $response->assertStatus(200);

        // Assert column header is present
        $response->assertSee('Held Timeslots');

        // Assert 3 held slots badge is visible for this client
        $response->assertSee('3 held slots');

        // Assert Quick stats card for Held Slots is visible
        $response->assertSee('Held Slots');
    }

    public function test_user_management_filter_by_has_held(): void
    {
        // Create held booking for this client
        Booking::create([
            'booking_reference' => 'PF-TEST-EXP3',
            'user_id' => $this->client->id,
            'customer_name' => $this->client->name,
            'customer_phone' => '+63 918 000 0001',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'total_hours' => 1,
            'rate_per_hour' => 500,
            'total_amount' => 500,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'expired',
        ]);

        // Another client without any held bookings
        $cleanClient = User::factory()->create([
            'name' => 'Clean Customer',
            'email' => 'clean@example.com',
            'role' => 'client',
            'is_active' => true,
        ]);

        // Filter by has_held
        $response = $this->actingAs($this->owner)->get(route('owner.users.index', ['status' => 'has_held']));
        $response->assertStatus(200);
        $response->assertSee($this->client->name);
        $response->assertDontSee($cleanClient->name);
    }

    public function test_user_management_sort_by_most_held_timeslots(): void
    {
        // Client A has 1 held slot
        Booking::create([
            'booking_reference' => 'PF-SORT-1',
            'user_id' => $this->client->id,
            'customer_name' => $this->client->name,
            'customer_phone' => '+63 918 000 0001',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'total_hours' => 1,
            'rate_per_hour' => 500,
            'total_amount' => 500,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'expired',
        ]);

        // Client B has 5 held slots
        $frequentAbandoner = User::factory()->create([
            'name' => 'Frequent Abandoner',
            'email' => 'abandoner@example.com',
            'role' => 'client',
            'is_active' => true,
        ]);

        Booking::create([
            'booking_reference' => 'PF-SORT-5',
            'user_id' => $frequentAbandoner->id,
            'customer_name' => $frequentAbandoner->name,
            'customer_phone' => '+63 918 000 0002',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '15:00',
            'total_hours' => 5,
            'rate_per_hour' => 500,
            'total_amount' => 2500,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'expired',
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.users.index', ['sort' => 'held_desc']));
        $response->assertStatus(200);

        // Frequent abandoner should appear before client A
        $content = $response->getContent();
        $posAbandoner = strpos($content, 'Frequent Abandoner');
        $posClient = strpos($content, $this->client->name);

        $this->assertNotFalse($posAbandoner);
        $this->assertNotFalse($posClient);
        $this->assertLessThan($posClient, $posAbandoner);
    }

    public function test_user_held_slots_ajax_endpoint_returns_json_history(): void
    {
        $booking = Booking::create([
            'booking_reference' => 'PF-JSON-TEST',
            'user_id' => $this->client->id,
            'customer_name' => $this->client->name,
            'customer_phone' => '+63 918 000 0001',
            'court_id' => $this->court->id,
            'booking_date' => today()->toDateString(),
            'start_time' => '13:00',
            'end_time' => '15:00',
            'total_hours' => 2,
            'rate_per_hour' => 500,
            'total_amount' => 1000,
            'payment_method' => 'paymongo',
            'payment_status' => 'unpaid',
            'booking_status' => 'expired',
        ]);

        BookingSlot::create([
            'booking_id' => $booking->id,
            'court_id' => $this->court->id,
            'date' => today()->toDateString(),
            'slot_time' => '13:00',
            'rate_per_hour' => 500,
            'status' => 'released',
        ]);
        BookingSlot::create([
            'booking_id' => $booking->id,
            'court_id' => $this->court->id,
            'date' => today()->toDateString(),
            'slot_time' => '14:00',
            'rate_per_hour' => 500,
            'status' => 'released',
        ]);

        $response = $this->actingAs($this->owner)->get(route('owner.users.held_slots', $this->client->id));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ],
            'total_held_slots' => 2,
        ]);

        $data = $response->json();
        $this->assertCount(1, $data['bookings']);
        $this->assertEquals('PF-JSON-TEST', $data['bookings'][0]['reference']);
        $this->assertEquals(2, $data['bookings'][0]['total_hours']);
        $this->assertEquals(['13:00', '14:00'], $data['bookings'][0]['slots']);
    }

    public function test_regular_client_is_forbidden_from_viewing_held_slots_endpoint(): void
    {
        $otherClient = User::factory()->create(['role' => 'client']);
        $response = $this->actingAs($this->client)->get(route('owner.users.held_slots', $otherClient->id));
        $response->assertStatus(403);
    }
}
