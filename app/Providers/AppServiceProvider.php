<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <-- ADD THIS LINE

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
        // --- THIS IS THE FIX ---
        // This line tells Laravel to use the Bootstrap 4 styling
        // for all pagination links throughout your entire application.
        Paginator::useBootstrapFour();
    }
}