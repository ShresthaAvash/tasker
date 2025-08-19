<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AssignedTask;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the staff member's tasks, supporting multiple views and filters.
     */
    public function index(Request $request)
    {
        $staffId = Auth::id();
        $viewType = $request->get('view_type', 'client');
        $search = $request->get('search');

        // --- 1. Determine Date Range ---
        if ($request->get('use_custom_range') === 'true') {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfDay();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();
        } else {
            $year = $request->get('year', now()->year);
            $month = $request->get('month', now()->month);
            if ($month === 'all') {
                $startDate = Carbon::create($year)->startOfYear();
                $endDate = Carbon::create($year)->endOfYear();
            } else {
                $startDate = Carbon::create($year, (int)$month, 1)->startOfMonth();
                $endDate = Carbon::create($year, (int)$month, 1)->endOfMonth();
            }
        }

        // --- 2. Data Fetching & Expansion ---
        [$taskInstances, $personalTasks] = $this->getTaskInstancesInDateRange($staffId, $startDate, $endDate, $search);

        $allStatuses = ['to_do' => 'To Do', 'ongoing' => 'Ongoing', 'completed' => 'Completed'];
        
        // --- 3. Handle AJAX requests for view updates ---
        if ($request->ajax()) {
            if ($viewType === 'client') {
                $clientTaskGroups = $taskInstances->sortBy('due_date_instance')->groupBy(['client.name', 'service.name', 'job.name'], true);
                return view('Staff.tasks._client_view', compact('clientTaskGroups', 'personalTasks', 'allStatuses'));
            } else { // time view
                $allFlatTasks = $this->prepareTimeViewTasks($taskInstances, $personalTasks);
                $paginatedTasks = $this->paginateCollection($allFlatTasks, 15, $request);
                return view('Staff.tasks._time_view', compact('paginatedTasks', 'allStatuses'));
            }
        }
        
        // --- 4. Initial Page Load ---
        $clientTaskGroups = $taskInstances->sortBy('due_date_instance')->groupBy(['client.name', 'service.name', 'job.name'], true);
        $years = range(now()->year - 4, now()->year + 2);
        $months = [ 'all' => 'All Months', 1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
        
        return view('Staff.tasks.index', compact('clientTaskGroups', 'personalTasks', 'allStatuses', 'startDate', 'endDate', 'years', 'months'));
    }

    /**
     * Fetches and expands all task instances within a given date range for a staff member.
     */
    private function getTaskInstancesInDateRange($staffId, Carbon $startDate, Carbon $endDate, $search)
    {
        // Assigned (Client) Tasks
        $assignedTasksQuery = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
            ->with(['client', 'job', 'service'])
            ->where(function ($query) {
                // Get non-recurring tasks that are not yet complete OR any recurring task.
                $query->where('is_recurring', false)->where('status', '!=', 'completed')
                      ->orWhere('is_recurring', true);
            })
            ->where('start', '<=', $endDate) // Task must have started before the end of the range
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate)); // and end after the start of the range (or have no end date)
        
        if ($search) {
            $assignedTasksQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }
        
        $taskInstances = new Collection();
        foreach ($assignedTasksQuery->get() as $task) {
            $this->expandRecurringTask($task, $startDate, $endDate, $taskInstances);
        }

        // Personal (Non-Client) Tasks
        $personalTasksQuery = Task::where('staff_id', $staffId)->whereNull('job_id')
            ->where(function ($query) {
                $query->where('is_recurring', false)->where('status', '!=', 'completed')
                      ->orWhere('is_recurring', true);
            })
            ->where('start', '<=', $endDate)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate))
            ->when($search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"));

        $personalTasks = new Collection();
        foreach($personalTasksQuery->get() as $task) {
            $this->expandRecurringTask($task, $startDate, $endDate, $personalTasks, true);
        }

        return [$taskInstances, $personalTasks];
    }

    /**
     * Expands a recurring task into individual instances for a given date range.
     */
    private function expandRecurringTask($task, $startDate, $endDate, &$collection, $isPersonal = false)
    {
        $task->is_personal = $isPersonal;

        if ($task->is_recurring && $task->start && $task->end) {
            $completedDates = (array) ($task->completed_at_dates ?? []);
            $cursor = $task->start->copy();

            while ($cursor->lte($task->end)) {
                // Only create an instance if it falls within our view's date range and is not marked as complete.
                if ($cursor->between($startDate, $endDate) && !in_array($cursor->toDateString(), $completedDates)) {
                    $instance = clone $task;
                    $instance->due_date_instance = $cursor->copy(); // This holds the specific date for this instance.
                    $collection->push($instance);
                }
                
                if ($cursor > $endDate) break; // Optimization: stop if we've passed the viewable range.
                
                switch ($task->recurring_frequency) {
                    case 'daily': $cursor->addDay(); break;
                    case 'weekly': $cursor->addWeek(); break;
                    case 'monthly': $cursor->addMonthWithNoOverflow(); break;
                    case 'yearly': $cursor->addYearWithNoOverflow(); break;
                    default: break 2; // Exit the loop if frequency is unknown.
                }
            }
        } elseif ($task->start && $task->start->between($startDate, $endDate)) {
            // For non-recurring tasks, just add it to the collection.
            $task->due_date_instance = $task->start;
            $collection->push($task);
        }
    }
    
    /**
     * Prepares a flat, sorted collection of all tasks for the 'Time View'.
     */
    private function prepareTimeViewTasks($taskInstances, $personalTasks)
    {
        $allTasks = new Collection($taskInstances);
        
        // Ensure personal tasks also have a due_date_instance for consistent sorting.
        $personalTasks->each(function ($task) {
            $task->due_date_instance = $task->start;
        });
        
        return $allTasks->concat($personalTasks)->sortBy('due_date_instance');
    }

    /**
     * Manually paginates a Laravel Collection.
     */
    private function paginateCollection(Collection $collection, int $perPage, Request $request): LengthAwarePaginator
    {
        $currentPage = Paginator::resolveCurrentPage('page');
        $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator($currentPageItems, $collection->count(), $perPage, $currentPage, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
        return $paginated->appends($request->all());
    }

    /**
     * Helper to retrieve a task (personal or assigned) based on a composite ID from the front-end.
     */
    private function getTask($taskId)
    {
        if (empty($taskId) || !str_contains($taskId, '_')) return null;

        [$type, $id] = explode('_', $taskId);

        if ($type === 'p' && $id) {
            return Task::where('id', $id)->where('staff_id', Auth::id())->first();
        }

        if ($type === 'a' && $id) {
            return AssignedTask::where('id', $id)->whereHas('staff', fn($q) => $q->where('users.id', Auth::id()))->first();
        }

        return null;
    }

    /**
     * Updates the status of a task instance.
     */
    public function updateStatus(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:to_do,ongoing,completed',
            'instance_date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid data provided.'], 422);
        }

        $task = $this->getTask($taskId);
        if (!$task) {
            return response()->json(['error' => 'Task not found or you are not authorized.'], 404);
        }
        
        if ($task->timer_started_at) {
            return response()->json(['error' => 'Please stop the timer before changing the status.'], 422);
        }

        $newStatus = $request->input('status');
        $instanceDate = $request->input('instance_date');

        if ($task->is_recurring) {
            if (!$instanceDate) {
                return response()->json(['error' => 'Instance date is required for recurring tasks.'], 422);
            }
            
            $completedDates = (array) ($task->completed_at_dates ?? []);

            if ($newStatus === 'completed') {
                // Add the date to the completed list if it's not already there.
                if (!in_array($instanceDate, $completedDates)) {
                    $completedDates[] = $instanceDate;
                }
            } else {
                // If the status is changing from completed, remove it from the list
                // and update the parent task's status to reflect it's active again.
                $completedDates = array_filter($completedDates, fn($date) => $date !== $instanceDate);
                $task->status = $newStatus;
            }
            
            $task->completed_at_dates = array_values(array_unique($completedDates));
        } else {
            // For non-recurring tasks, simply update the status.
            $task->status = $newStatus;
        }

        $task->save();

        return response()->json(['success' => 'Status updated successfully!']);
    }

    /**
     * Starts the timer for a specific task.
     */
    public function startTimer(Request $request, $taskId)
    {
        $task = $this->getTask($taskId);
        if (!$task) {
            return response()->json(['error' => 'Task not found.'], 404);
        }
        if ($task->status !== 'ongoing') {
            return response()->json(['error' => 'Task must be "ongoing" to start the timer.'], 422);
        }

        // Stop any other running timers for this user first.
        $this->stopAllRunningTimersForUser(Auth::id(), $taskId);

        $task->timer_started_at = Carbon::now();
        $task->save();

        return response()->json([
            'success' => 'Timer started successfully.',
            'timer_started_at' => $task->timer_started_at->toIso8601String(),
            'duration_in_seconds' => $task->duration_in_seconds,
        ]);
    }

    /**
     * Stops the timer for a specific task.
     */
    public function stopTimer(Request $request, $taskId)
    {
        $task = $this->getTask($taskId);
        if (!$task || !$task->timer_started_at) {
            return response()->json(['error' => 'Timer is not running for this task.'], 400);
        }

        $currentDuration = (int) $task->duration_in_seconds;
        $startTime = $task->timer_started_at->getTimestamp();
        $stopTime = Carbon::now()->getTimestamp();
        $elapsed = $stopTime - $startTime;
        
        if ($elapsed > 0) {
            $task->duration_in_seconds = $currentDuration + $elapsed;
        }

        $task->timer_started_at = null;
        $task->save();

        return response()->json([
            'success' => 'Timer stopped successfully.',
            'new_duration' => $task->duration_in_seconds,
        ]);
    }
    
    /**
     * Stops all currently running timers for a user, excluding a specific task.
     */
    private function stopAllRunningTimersForUser($userId, $excludeTaskId)
    {
        $stopTime = Carbon::now()->getTimestamp();
        
        $updateTask = function ($task) use ($stopTime, $excludeTaskId) {
            $currentTaskId = ($task instanceof Task ? 'p_' : 'a_') . $task->id;
            if ($currentTaskId === $excludeTaskId) {
                return; // Don't stop the timer for the task we are about to start.
            }

            if ($task->timer_started_at) {
                $currentDuration = (int) $task->duration_in_seconds;
                $startTime = $task->timer_started_at->getTimestamp();
                $elapsed = $stopTime - $startTime;
                
                if ($elapsed > 0) {
                    $task->duration_in_seconds = $currentDuration + $elapsed;
                }
                
                $task->timer_started_at = null;
                $task->save();
            }
        };

        Task::where('staff_id', $userId)->whereNotNull('timer_started_at')->get()->each($updateTask);
        AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $userId))->whereNotNull('timer_started_at')->get()->each($updateTask);
    }

    /**
     * Adds a manual amount of time to a task's duration.
     */
    public function addManualTime(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'hours' => 'required|integer|min:0',
            'minutes' => 'required|integer|min:0|max:59',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid time format.'], 422);
        }

        $task = $this->getTask($taskId);
        if (!$task) {
            return response()->json(['error' => 'Task not found.'], 404);
        }

        if ($task->timer_started_at) {
            return response()->json(['error' => 'Please stop the live timer before adding manual time.'], 422);
        }

        $secondsToAdd = ($request->input('hours') * 3600) + ($request->input('minutes') * 60);

        if ($secondsToAdd <= 0) {
             return response()->json(['error' => 'Please enter a positive amount of time.'], 422);
        }

        $task->duration_in_seconds = ($task->duration_in_seconds ?? 0) + $secondsToAdd;
        $task->save();

        return response()->json([
            'success' => 'Manual time added successfully.',
            'new_duration' => $task->duration_in_seconds,
        ]);
    }
}