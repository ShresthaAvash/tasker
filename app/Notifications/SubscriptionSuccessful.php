<?php

namespace App\Notifications;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionSuccessful extends Notification
{
    use Queueable;

    protected $user;
    protected $plan;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, Plan $plan)
    {
        $this->user = $user;
        $this->plan = $plan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // We only want to send this as an email
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // --- THIS IS THE DEFINITIVE FIX ---
        // This now points to our beautiful new Markdown email template.
        return (new MailMessage)
                    ->subject('Welcome! Your Subscription is Active')
                    ->markdown('emails.subscription_successful', [
                        'user' => $this->user,
                        'plan' => $this->plan,
                    ]);
        // --- END OF FIX ---
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}