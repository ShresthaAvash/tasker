<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class UpdateTaskStatuses extends Command
{
    protected $signature = 'tasks:update-status';
    protected $description = 'Checks for active tasks whose deadline has passed and marks them as inactive';

    public function handle()
    {
        $this->info('Checking for overdue tasks...');

        $overdueTasks = Task::where('status', 'active')
            ->whereNotNull('end')
            ->where('end', '<', Carbon::now())
            ->get();

        foreach ($overdueTasks as $task) {
            $task->update(['status' => 'inactive']);
            $this->info("Task #{$task->id} ('{$task->name}') marked as inactive.");
        }

        $this->info('Done.');
        return 0;
    }
}