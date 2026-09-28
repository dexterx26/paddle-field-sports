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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('court_owner_id')->nullable()->after('role')->constrained('users')->nullOnDelete();
            $table->json('permissions')->nullable()->after('court_owner_id');
            $table->boolean('is_active')->default(true)->after('permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['court_owner_id']);
            $table->dropColumn(['court_owner_id', 'permissions', 'is_active']);
        });
    }
};
