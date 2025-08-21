<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use App\Models\Task;
use App\Models\AssignedTask;
use Carbon\Carbon;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;
use App\Http\View\Composers\GlobalComposer; // <-- NEW
use Laravel\Cashier\Cashier; // <-- ADD THIS

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
        
        // Cashier::routes(); // <-- THIS LINE HAS BEEN REMOVED

        // --- THIS IS THE FIX: The old, conflicting timer composer has been removed ---

        /**
         * View Composer for Super Admin subscription request badge
         */
        View::composer('vendor.adminlte.page', function ($view) {
            if (Auth::check() && Auth::user()->type === 'S') {
                $requestCount = User::where('type', 'O')->where('status', 'R')->count();

                if ($requestCount > 0) {
                    $view->getFactory()->startPush(
                        'menu_items',
                        "<li class='nav-header bg-warning'>PENDING REQUESTS: {$requestCount}</li>"
                    );
                }
            }
        });

        /**
         * Global Composer (new incoming code)
         * Runs for ALL views, making $runningTimer available everywhere
         */
        View::composer('*', GlobalComposer::class);
    }
}