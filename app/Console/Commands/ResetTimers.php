<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use App\Models\AssignedTask;
use Illuminate\Support\Facades\Auth;

class ResetTimers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * php artisan timers:reset {--user=} {--hard}
     */
    protected $signature = 'timers:reset {--user=} {--hard : Also reset status to to_do}';

    /**
     * The console command description.
     */
    protected $description = 'Force stop all running timers for a user to fix stuck timer issues';

    public function handle()
    {
        $userId = $this->option('user');

        if (!$userId) {
            $this->error("You must pass a --user=<id> option.");
            return Command::FAILURE;
        }

        $hardReset = $this->option('hard');

        $this->info("Stopping all timers for user ID: {$userId}");

        // Personal tasks
        $personalTasks = Task::where('staff_id', $userId)
            ->whereNotNull('timer_started_at')
            ->get();

        foreach ($personalTasks as $task) {
            $task->timer_started_at = null;
            if ($hardReset) {
                $task->status = 'to_do';
            }
            $task->save();
        }

        // Assigned tasks
        $assignedTasks = AssignedTask::whereHas('staff', function ($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->whereNotNull('timer_started_at')
            ->get();

        foreach ($assignedTasks as $task) {
            $task->timer_started_at = null;
            if ($hardReset) {
                $task->status = 'to_do';
            }
            $task->save();
        }

        $this->info("✅ Timers reset successfully for user ID {$userId}");
        if ($hardReset) {
            $this->warn("⚠ Status for tasks was also reset to 'to_do'");
        }

        return Command::SUCCESS;
    }
}