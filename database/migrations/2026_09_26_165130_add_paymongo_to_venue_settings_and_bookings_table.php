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
        Schema::table('venue_settings', function (Blueprint $table) {
            $table->string('paymongo_secret_key')->nullable()->after('xendit_simulation_mode');
            $table->string('paymongo_public_key')->nullable()->after('paymongo_secret_key');
            $table->string('paymongo_webhook_token')->nullable()->after('paymongo_public_key');
            $table->boolean('paymongo_simulation_mode')->default(true)->after('paymongo_webhook_token');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->string('paymongo_checkout_id')->nullable()->after('xendit_payment_url');
            $table->string('paymongo_payment_url')->nullable()->after('paymongo_checkout_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venue_settings', function (Blueprint $table) {
            $table->dropColumn([
                'paymongo_secret_key',
                'paymongo_public_key',
                'paymongo_webhook_token',
                'paymongo_simulation_mode'
            ]);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'paymongo_checkout_id',
                'paymongo_payment_url'
            ]);
        });
    }
};
