<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Plan;
use App\Notifications\OrganizationSubscribed;
use App\Notifications\SubscriptionSuccessful;
use Illuminate\Support\Facades\Notification;

class PostSubscriptionActions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public User $user, public Plan $plan)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Update user status and organization_id
        $this->user->status = 'A';
        $this->user->organization_id = $this->user->id;
        $this->user->save();

        // 2. Send notification to Super Admins
        $superAdmins = User::where('type', 'S')->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new OrganizationSubscribed($this->user, $this->plan));
        }

        // 3. Send confirmation email to the user
        $this->user->notify(new SubscriptionSuccessful($this->user, $this->plan));
    }
}