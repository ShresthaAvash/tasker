<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\AssignedTask;

class GlobalComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        if (!Auth::check() || Auth::user()->type !== 'T') {
            $view->with('runningTimer', null);
            return;
        }

        $userId = Auth::id();
        $runningTask = null;

        // Check for a running personal task first
        $runningTask = Task::where('staff_id', $userId)
            ->whereNotNull('timer_started_at')
            ->first();

        // If no personal task is running, check for an assigned task
        if (!$runningTask) {
            $runningTask = AssignedTask::whereHas('staff', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->whereNotNull('timer_started_at')
            ->first();
        }
        
        // Add a composite 'timer_task_id' to make it easy for our AJAX calls
        if ($runningTask) {
            $runningTask->timer_task_id = ($runningTask instanceof Task ? 'p_' : 'a_') . $runningTask->id;
        }

        $view->with('runningTimer', $runningTask);
    }
}