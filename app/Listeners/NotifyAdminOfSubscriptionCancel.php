<?php

namespace App\Listeners;

use Laravel\Cashier\Events\SubscriptionUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class NotifyAdminOfSubscriptionCancel implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionUpdated $event): void
    {
        $subscription = $event->subscription;

        // Check if the subscription was just canceled
        if ($subscription->canceled()) {
            $organization = $subscription->owner;
            $superAdmins = User::where('type', 'S')->get();

            if ($superAdmins->isNotEmpty()) {
                // You would typically create a dedicated Notification class for this,
                // but for simplicity, we'll send a basic one.
                Notification::send($superAdmins, new \App\Notifications\SimpleAdminNotification(
                    "Subscription Canceled: {$organization->name}",
                    "The subscription for '{$organization->name}' has been canceled. It will remain active until {$subscription->ends_at->format('F d, Y')}."
                ));
            }
        }
    }
}