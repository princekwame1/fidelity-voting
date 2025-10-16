<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Models\Event;

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
        // Custom route model binding for encrypted event IDs
        Route::bind('event', function ($value) {
            // Try to find by encrypted ID first
            $event = Event::findByEncryptedId($value);

            // If not found, try normal ID for backwards compatibility
            if (!$event) {
                $event = Event::find($value);
            }

            return $event ?: abort(404);
        });
    }
}
