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
        // 1. Add credits to users table
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('credits', 10, 2)->default(0.00)->after('avatar');
        });

        // 2. Add credits_per_hour to courts table
        Schema::table('courts', function (Blueprint $table) {
            $table->decimal('credits_per_hour', 10, 2)->default(1.00)->after('price_per_hour');
        });

        // 3. Add credit conversion rate to venue_settings table
        Schema::table('venue_settings', function (Blueprint $table) {
            $table->decimal('credit_conversion_rate', 10, 2)->default(150.00)->after('currency_symbol'); // e.g. 150 PHP = 1 Credit
            $table->boolean('credits_enabled')->default(true)->after('credit_conversion_rate');
        });

        // 4. Add credits_used to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('credits_used', 10, 2)->default(0.00)->after('total_amount');
        });

        // 5. Create credit_topups table for manual receipt uploads & owner approval
        Schema::create('credit_topups', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // e.g. CR-20261016-XXXXX
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount_php', 10, 2); // Amount in PHP (e.g. 150.00, 300.00)
            $table->decimal('credits', 10, 2);    // Equivalent credits (e.g. 1.00, 2.00)
            $table->decimal('conversion_rate', 10, 2)->default(150.00); // Snapshot rate
            $table->string('payment_method')->default('manual_receipt'); // gcash, maya, bank_transfer
            $table->string('receipt_image_path');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('customer_notes')->nullable();
            $table->timestamps();
        });

        // 6. Create credit_transactions ledger table
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['topup', 'booking_payment', 'booking_refund', 'adjustment']);
            $table->decimal('amount_credits', 10, 2); // Positive for addition, negative for deduction
            $table->decimal('balance_after', 10, 2);
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable(); // CreditTopup, Booking
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('credit_topups');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('credits_used');
        });

        Schema::table('venue_settings', function (Blueprint $table) {
            $table->dropColumn(['credit_conversion_rate', 'credits_enabled']);
        });

        Schema::table('courts', function (Blueprint $table) {
            $table->dropColumn('credits_per_hour');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credits');
        });
    }
};
