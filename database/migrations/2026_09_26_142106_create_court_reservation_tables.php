<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Venue & Owner Global Settings
        Schema::create('venue_settings', function (Blueprint $table) {
            $table->id();
            $table->string('venue_name')->default('Paddle Field Sports Center');
            $table->string('tagline')->default('Premier Indoor & Outdoor Pickleball Facility');
            $table->text('description')->nullable();
            $table->string('phone')->nullable()->default('+63 917 555 7233');
            $table->string('email')->nullable()->default('contact@paddlefield.com');
            $table->string('address')->nullable()->default('Bacal 3, Talavera, Nueva Ecija');
            $table->string('opening_time')->default('06:00');
            $table->string('closing_time')->default('00:00');
            $table->string('currency')->default('PHP');
            $table->string('currency_symbol')->default('₱');
            // Payment configuration: 'xendit' or 'manual_receipt'
            $table->string('payment_mode')->default('manual_receipt');
            $table->string('xendit_secret_key')->nullable();
            $table->string('xendit_public_key')->nullable();
            $table->string('xendit_webhook_token')->nullable();
            $table->boolean('xendit_simulation_mode')->default(true);
            // Manual Receipt payment info
            $table->string('manual_bank_name')->nullable()->default('GCash / Maya / BDO');
            $table->string('manual_account_name')->nullable()->default('Paddle Field Sports Center');
            $table->string('manual_account_number')->nullable()->default('0917-555-7233');
            $table->text('manual_payment_instructions')->nullable();
            $table->string('manual_payment_qr')->nullable();
            // Holding time in seconds (requirement: 2 mins = 120s)
            $table->integer('holding_duration_seconds')->default(120);
            $table->timestamps();
        });

        // Courts table
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Court 1, Court 2, Court 3
            $table->integer('court_number')->default(1);
            $table->text('description')->nullable();
            $table->enum('type', ['indoor', 'outdoor'])->default('indoor');
            $table->string('surface_type')->default('Pro Cushion Acrylic');
            $table->decimal('price_per_hour', 10, 2)->default(150.00);
            $table->integer('max_players')->default(4);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Facility photos for main website gallery
        Schema::create('facility_photos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category')->default('court'); // court, lounge, amenity, event
            $table->string('image_path');
            $table->text('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(true);
            $table->timestamps();
        });

        // Bookings table
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique(); // e.g. PF-20260926-XXXX
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->date('booking_date');
            $table->string('start_time'); // '08:00'
            $table->string('end_time');   // '10:00'
            $table->integer('total_hours')->default(1);
            // Snapshot of court rate at the moment of booking so future edits won't affect old bookings
            $table->decimal('rate_per_hour', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->integer('players_count')->default(4);
            $table->text('notes')->nullable();
            $table->enum('payment_method', ['xendit', 'manual_receipt', 'cash'])->default('manual_receipt');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            // Booking statuses: held (2-min hold), pending_approval, confirmed, rejected, cancelled, expired
            $table->enum('booking_status', [
                'held',
                'pending_approval',
                'confirmed',
                'rejected',
                'cancelled',
                'expired'
            ])->default('pending_approval');
            $table->timestamp('held_until')->nullable();
            $table->string('receipt_image_path')->nullable();
            $table->timestamp('receipt_uploaded_at')->nullable();
            $table->string('xendit_invoice_id')->nullable();
            $table->string('xendit_payment_url')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // Individual hourly slots for fast conflict detection and calendar rendering
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('court_id')->constrained('courts')->cascadeOnDelete();
            $table->date('date');
            $table->string('slot_time'); // '08:00', '09:00', etc.
            $table->decimal('rate_per_hour', 10, 2);
            $table->enum('status', ['held', 'pending_approval', 'confirmed', 'released'])->default('pending_approval');
            $table->timestamp('held_until')->nullable();
            $table->timestamps();

            $table->index(['court_id', 'date', 'slot_time', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_slots');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('facility_photos');
        Schema::dropIfExists('courts');
        Schema::dropIfExists('venue_settings');
    }
};
