<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class MessageSentToClients extends Notification
{
    use Queueable;

    protected $organization;
    protected $subject;
    protected $message;
    protected $recipients;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $organization, string $subject, string $message, Collection $recipients)
    {
        $this->organization = $organization;
        $this->subject = $subject;
        $this->message = $message;
        $this->recipients = $recipients;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
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
            'message' => "Your message with the subject '{$this->subject}' has been sent.",
            'full_message' => $this->message, // Keep the original message
            'recipients' => $this->recipients->pluck('name')->implode(', '),
        ];
    }
}