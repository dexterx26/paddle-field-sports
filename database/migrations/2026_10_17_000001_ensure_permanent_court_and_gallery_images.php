<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update courts with permanent tracked image paths unless they have a custom external HTTP(S) URL
        $courts = DB::table('courts')->get();
        foreach ($courts as $court) {
            $num = $court->court_number ?? $court->id;
            if (empty($court->image_path) || !str_starts_with($court->image_path, 'http')) {
                DB::table('courts')
                    ->where('id', $court->id)
                    ->update([
                        'image_path' => "courts/court-{$num}.jpg"
                    ]);
            }
        }

        // Update default facility photos if they still point to missing sample files
        DB::table('facility_photos')
            ->where('image_path', 'gallery/court-action.jpg')
            ->orWhereNull('image_path')
            ->update(['image_path' => 'gallery/court-action.jpg']);

        DB::table('facility_photos')
            ->where('image_path', 'gallery/club-lounge.jpg')
            ->update(['image_path' => 'gallery/club-lounge.jpg']);

        DB::table('facility_photos')
            ->where('image_path', 'gallery/outdoor-sunset.jpg')
            ->update(['image_path' => 'gallery/entrance-sign.jpg']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed
    }
};
