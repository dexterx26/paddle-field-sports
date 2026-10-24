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
            $table->string('tagline')->nullable()->default('Premier Indoor Pickleball Facility')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venue_settings', function (Blueprint $table) {
            $table->string('tagline')->nullable(false)->default('Premier Indoor Pickleball Facility')->change();
        });
    }
};
