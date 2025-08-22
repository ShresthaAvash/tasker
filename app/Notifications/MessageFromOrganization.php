<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class MessageFromOrganization extends Notification
{
    use Queueable;

    protected $organization;
    protected $subject;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $organization, string $subject, string $message)
    {
        $this->organization = $organization;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // We only want to store it in the database for now
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'organization_name' => $this->organization->name,
            'subject' => $this->subject,
            'message' => $this->message,
        ];
    }
}