<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use App\Listeners\LogSuccessfulLogin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
// REMOVED: The 'use' statements for BuildingMenu and BuildMenuNotifications
use Laravel\Cashier\Events\SubscriptionUpdated; // <-- ADD THIS
use App\Listeners\NotifyAdminOfSubscriptionCancel; // <-- ADD THIS

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        Login::class => [
            LogSuccessfulLogin::class,
        ],

        // --- THIS IS THE FIX: Listen for Cashier's event with our new listener ---
        SubscriptionUpdated::class => [
            NotifyAdminOfSubscriptionCancel::class,
        ],
        // --- END OF FIX ---
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}