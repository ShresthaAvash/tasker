<?php

namespace App\Notifications;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrganizationSubscribed extends Notification
{
    use Queueable;

    protected $organization;
    protected $plan;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $organization, Plan $plan)
    {
        $this->organization = $organization;
        $this->plan = $plan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Store in the database
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'organization_id' => $this->organization->id,
            'organization_name' => $this->organization->name,
            'message' => "'{$this->organization->name}' has subscribed to the '{$this->plan->name}' plan.",
        ];
    }
}