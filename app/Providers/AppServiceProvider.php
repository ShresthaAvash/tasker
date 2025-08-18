<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View; // <-- ADD THIS LINE
use App\Http\View\Composers\GlobalComposer; // <-- ADD THIS LINE

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
        Paginator::useBootstrapFour();

        // --- NEW CODE START ---
        // This tells Laravel to run our GlobalComposer for ALL views,
        // making the $runningTimer variable available everywhere.
        View::composer('*', GlobalComposer::class);
        // --- NEW CODE END ---
    }
}