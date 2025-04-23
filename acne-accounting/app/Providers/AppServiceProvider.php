<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Force HTTPS scheme for URL generation if the app is running in production
        // or if specifically configured to run under HTTPS via .env or server config.
        // This is often necessary when behind a reverse proxy like Cloudflare.
        if ($this->app->environment('production') || config('app.force_https')) {
             URL::forceScheme('https');
        }
    }
}
