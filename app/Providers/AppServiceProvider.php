<?php

namespace App\Providers;

use App\Models\User; // <-- ADD THIS LINE
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

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
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFour();

        // View Composer for the main AdminLTE layout
        View::composer('vendor.adminlte.page', function ($view) {
            
            // --- Logic for the Subscription Request Badge ---
            if (Auth::check() && Auth::user()->type === 'S') {
                $requestCount = User::where('type', 'O')->where('status', 'R')->count();
                
                if ($requestCount > 0) {
                    $view->getFactory()->startPush('menu_items', "<li class='nav-header bg-warning'>PENDING REQUESTS: {$requestCount}</li>");
                }
            }

        });
    }
}