<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // <-- ADD THIS LINE
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\AssignedTask;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;

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
        Schema::defaultStringLength(191); // <-- ADD THIS LINE

        Paginator::useBootstrapFour();
        
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
    }
}