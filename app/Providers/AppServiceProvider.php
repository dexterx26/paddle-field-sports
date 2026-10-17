<?php

namespace App\Providers;

use App\Models\VenueSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            static $venueSettings = null;
            if ($venueSettings === null) {
                try {
                    if (Schema::hasTable('venue_settings')) {
                        $venueSettings = VenueSetting::getSettings();
                    }
                } catch (\Throwable $e) {
                    $venueSettings = null;
                }
            }

            if ($venueSettings) {
                $view->with('venueSettings', $venueSettings);
                $view->with('globalSettings', $venueSettings);
            }
        });
    }
}
