<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\Court;
use App\Models\FacilityPhoto;
use App\Models\User;
use App\Models\VenueSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Venue Settings
        $settings = VenueSetting::create([
            'venue_name' => 'Paddle Field Sports Center',
            'tagline' => 'Premier Indoor & Outdoor Pickleball Facility',
            'description' => 'Welcome to Paddle Field Sports Center — the leading destination for competitive and social pickleball. Featuring tournament-grade cushion acrylic courts, high-lumen anti-glare LED lighting, luxury player lounge, and automated reservations.',
            'phone' => '+63 917 555 7233',
            'email' => 'info@paddlefieldsports.com',
            'address' => 'Bacal 3, Talavera, Nueva Ecija',
            'opening_time' => '06:00',
            'closing_time' => '00:00',
            'currency' => 'PHP',
            'currency_symbol' => '₱',
            'payment_mode' => 'manual_receipt', // Toggleable to 'xendit' by owner
            'xendit_simulation_mode' => true,
            'xendit_secret_key' => 'xnd_development_sample_key_test_123',
            'xendit_public_key' => 'xnd_public_development_sample_key_test_123',
            'xendit_webhook_token' => 'xendit_token_sample_secret_xyz',
            'manual_bank_name' => 'GCash / Maya / BDO Online',
            'manual_account_name' => 'Paddle Field Sports Center Inc.',
            'manual_account_number' => '0917-555-7233',
            'manual_payment_instructions' => "1. Send exact booking total via GCash or Maya to 0917-555-7233 or scan the QR standee.\n2. In the payment note, indicate your Booking Reference Code.\n3. Take a screenshot of the completed payment receipt.\n4. Upload the screenshot image below. The owner will review and confirm your slot promptly!",
            'manual_payment_qr' => 'qr/default-qr.jpg',
            'holding_duration_seconds' => 120, // 2 minutes hold for Xendit checkout
        ]);

        // 2. Create Users with specific roles
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '+63 917 000 0001',
        ]);

        $owner = User::create([
            'name' => 'Paddle Field Owner',
            'email' => 'owner@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'court_owner',
            'phone' => '+63 917 555 7233',
        ]);

        $assistant = User::create([
            'name' => 'Jane',
            'email' => 'assistant@paddlefield.com',
            'password' => Hash::make('password123'),
            'role' => 'admin_assistant',
            'court_owner_id' => $owner->id,
            'permissions' => ['schedule', 'approvals'], // default viewing of schedule and approval of reservation
            'is_active' => true,
            'phone' => '+63 919 123 4567',
        ]);

        $client = User::create([
            'name' => 'Michael Austria',
            'email' => 'player@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '+63 918 222 3344',
        ]);

        $client2 = User::create([
            'name' => 'Jeric Dela Cruz',
            'email' => 'player2@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '+63 918 555 6789',
        ]);

        $client3 = User::create([
            'name' => 'Paolo Soriano',
            'email' => 'paolo@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
            'phone' => '+63 918 777 8899',
        ]);

        // 3. Create Courts (Requirement: exactly 3 courts, all indoor, default 150 pesos/hr)
        $court1 = Court::create([
            'name' => 'Court 1 - Championship Center',
            'court_number' => 1,
            'description' => 'Indoor tournament-certified court with cushioned 9-layer acrylic surface and spectator gallery view.',
            'type' => 'indoor',
            'surface_type' => 'Pro Cushion Acrylic',
            'price_per_hour' => 1.00,
            'max_players' => 4,
            'image_path' => 'courts/court-1.jpg',
            'is_active' => true,
        ]);

        $court2 = Court::create([
            'name' => 'Court 2 - Pro Indoor',
            'court_number' => 2,
            'description' => 'Spacious indoor court with anti-glare floodlighting, player rest bench, and tournament acrylic court surface.',
            'type' => 'indoor',
            'surface_type' => 'Pro Cushion Acrylic',
            'price_per_hour' => 1.00,
            'max_players' => 4,
            'image_path' => 'courts/court-1.jpg',
            'is_active' => true,
        ]);

        $court3 = Court::create([
            'name' => 'Court 3 - Club Indoor',
            'court_number' => 3,
            'description' => 'Fully air-conditioned indoor court ideal for competitive doubles, coaching sessions, and private drills.',
            'type' => 'indoor',
            'surface_type' => 'Pro Cushion Acrylic',
            'price_per_hour' => 1.00,
            'max_players' => 4,
            'image_path' => 'courts/court-1.jpg',
            'is_active' => true,
        ]);

        // 4. Create Gallery Facility Photos
        FacilityPhoto::create([
            'title' => 'Championship Indoor Court Action',
            'category' => 'court',
            'image_path' => 'gallery/court-action.jpg',
            'caption' => 'Tournament-grade indoor playing surface with vibrant colors and professional netting.',
            'sort_order' => 1,
            'is_featured' => true,
        ]);

        FacilityPhoto::create([
            'title' => 'The Ace Lounge & Pro Shop',
            'category' => 'lounge',
            'image_path' => 'gallery/club-lounge.jpg',
            'caption' => 'Relax between matches with specialty coffee, refreshing drinks, and paddle gear boutique.',
            'sort_order' => 2,
            'is_featured' => true,
        ]);

        FacilityPhoto::create([
            'title' => 'Floodlit Evening Match on Court 2',
            'category' => 'court',
            'image_path' => 'gallery/outdoor-sunset.jpg',
            'caption' => 'Evening play under stadium lights with cool tropical breeze.',
            'sort_order' => 3,
            'is_featured' => true,
        ]);

        // 5. Sample Seed Bookings
        $today = Carbon::today()->format('Y-m-d');

        // Booking 1: Confirmed booking today 14:00 - 16:00 on Court 1
        $booking1 = Booking::create([
            'booking_reference' => 'PF-' . date('Ymd') . '-A101',
            'user_id' => $client->id,
            'customer_name' => $client->name,
            'customer_phone' => $client->phone,
            'customer_email' => $client->email,
            'court_id' => $court1->id,
            'booking_date' => $today,
            'start_time' => '14:00',
            'end_time' => '16:00',
            'total_hours' => 2,
            'rate_per_hour' => $court1->price_per_hour,
            'total_amount' => $court1->price_per_hour * 2,
            'players_count' => 4,
            'notes' => 'Weekly friendly doubles match',
            'payment_method' => 'xendit',
            'payment_status' => 'paid',
            'booking_status' => 'confirmed',
            'approved_at' => now(),
        ]);

        BookingSlot::create([
            'booking_id' => $booking1->id,
            'court_id' => $court1->id,
            'date' => $today,
            'slot_time' => '14:00',
            'rate_per_hour' => $court1->price_per_hour,
            'status' => 'confirmed',
        ]);
        BookingSlot::create([
            'booking_id' => $booking1->id,
            'court_id' => $court1->id,
            'date' => $today,
            'slot_time' => '15:00',
            'rate_per_hour' => $court1->price_per_hour,
            'status' => 'confirmed',
        ]);

        // Booking 2: Guest booking pending owner approval with receipt uploaded!
        $booking2 = Booking::create([
            'booking_reference' => 'PF-' . date('Ymd') . '-G789',
            'user_id' => null, // Guest booking (no registration required)
            'customer_name' => 'Dexter John Luciano (Guest)',
            'customer_phone' => '0917-889-1122',
            'customer_email' => 'dexterjohnluciano172@gmail.com',
            'court_id' => $court2->id,
            'booking_date' => $today,
            'start_time' => '18:00',
            'end_time' => '20:00',
            'total_hours' => 2,
            'rate_per_hour' => $court2->price_per_hour,
            'total_amount' => $court2->price_per_hour * 2,
            'players_count' => 4,
            'notes' => 'Guest reservation via GCash receipt upload',
            'payment_method' => 'manual_receipt',
            'payment_status' => 'unpaid',
            'booking_status' => 'pending_approval',
            'receipt_image_path' => 'receipts/sample-receipt.jpg',
            'receipt_uploaded_at' => now()->subMinutes(15),
        ]);

        BookingSlot::create([
            'booking_id' => $booking2->id,
            'court_id' => $court2->id,
            'date' => $today,
            'slot_time' => '18:00',
            'rate_per_hour' => $court2->price_per_hour,
            'status' => 'pending_approval',
        ]);
        BookingSlot::create([
            'booking_id' => $booking2->id,
            'court_id' => $court2->id,
            'date' => $today,
            'slot_time' => '19:00',
            'rate_per_hour' => $court2->price_per_hour,
            'status' => 'pending_approval',
        ]);
    }
}
