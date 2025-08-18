<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use App\Models\Task;
use App\Models\AssignedTask;
use Carbon\Carbon;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

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
        Paginator::useBootstrapFour();

        /**
         * View Composer for staff active timers
         */
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

                // 🔥 Share $activeTimer with all views
                $view->with('activeTimer', $activeTimer);
            }
        });

        /**
         * View Composer for Super Admin subscription request badge
         */
        View::composer('vendor.adminlte.page', function ($view) {
            if (Auth::check() && Auth::user()->type === 'S') {
                $requestCount = User::where('type', 'O')->where('status', 'R')->count();

                if ($requestCount > 0) {
                    $view->getFactory()->startPush(
                        'menu_items',
                        "<li class='nav-header bg-warning'>PENDING REQUESTS: {$requestCount}</li>"
                    );
                }
            }
        });
    }
}
