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
            $table->text('paymongo_secret_key')->nullable()->change();
            $table->text('paymongo_webhook_token')->nullable()->change();
            $table->text('xendit_secret_key')->nullable()->change();
            $table->text('xendit_webhook_token')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venue_settings', function (Blueprint $table) {
            $table->string('paymongo_secret_key', 255)->nullable()->change();
            $table->string('paymongo_webhook_token', 255)->nullable()->change();
            $table->string('xendit_secret_key', 255)->nullable()->change();
            $table->string('xendit_webhook_token', 255)->nullable()->change();
        });
    }
};
