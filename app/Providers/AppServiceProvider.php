<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View; // <-- Add this line
use Illuminate\Support\Facades\Auth; // <-- Add this line
use App\Models\Task;                  // <-- Add this line
use App\Models\AssignedTask;          // <-- Add this line
use Carbon\Carbon;                    // <-- Add this line

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
            Schema::defaultStringLength(191);

            // --- ADD THIS ENTIRE BLOCK ---
        // This shares the active timer data with all views when a staff member is logged in.
        View::composer('*', function ($view) {
            if (Auth::check() && Auth::user()->type === 'T') {
                $staffId = Auth::id();
                
                $activeTask = Task::where('staff_id', $staffId)
                    ->whereNotNull('timer_started_at')
                    ->first();

                $activeAssignedTask = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
                    ->whereNotNull('timer_started_at')
                    ->with('client')
                    ->first();
                
                $activeTimer = null;
                $task = $activeTask ?? $activeAssignedTask;

                if ($task) {
                    $isPersonal = $task instanceof Task;
                    $taskId = ($isPersonal ? 'p_' : 'a_') . $task->id;
                    $taskName = $isPersonal ? $task->name : (optional($task->client)->name . ': ' . $task->name);

                    $activeTimer = [
                        'task_id' => $taskId,
                        'task_name' => $taskName,
                        'duration_in_seconds' => $task->duration_in_seconds,
                        'timer_started_at' => $task->timer_started_at->toIso8601String(),
                    ];
                }

                $view->with('activeTimer', $activeTimer);
            }
        });
        // --- END OF BLOCK ---

    }
}
