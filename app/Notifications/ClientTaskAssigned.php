<?php

namespace App\Notifications;

use App\Models\AssignedTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientTaskAssigned extends Notification
{
    use Queueable;

    protected $assignedTask;

    /**
     * Create a new notification instance.
     */
    public function __construct(AssignedTask $assignedTask)
    {
        $this->assignedTask = $assignedTask;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database']; // We only want to store it in the database
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        // Ensure the client relationship is loaded for the message
        $this->assignedTask->loadMissing('client');

        return [
            'assigned_task_id' => $this->assignedTask->id,
            'task_name' => $this->assignedTask->name,
            'client_name' => optional($this->assignedTask->client)->name,
            'message' => "You have a new task '{$this->assignedTask->name}' for client " . optional($this->assignedTask->client)->name . ".",
        ];
    }
}