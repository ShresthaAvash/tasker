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
        
        // --- THIS IS THE DEFINITIVE FIX for STATUS FILTERING ---
        $statuses = $request->input('statuses');

        // Default to 'to_do' and 'ongoing' ONLY if the statuses parameter is completely absent from the request.
        if ($statuses === null) {
            $statuses = ['to_do', 'ongoing'];
        }
        // If the user de-selects everything, the frontend will send an empty array.
        // We must ensure it's an array to prevent errors.
        if (!is_array($statuses)) {
            $statuses = [];
        }
        // --- END OF FIX ---

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
        [$taskInstances, $personalTasks] = $this->getTaskInstancesInDateRange($staffId, $startDate, $endDate, $search, $statuses);

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
    private function getTaskInstancesInDateRange($staffId, Carbon $startDate, Carbon $endDate, $search, $statuses)
    {
        // Assigned (Client) Tasks
        $assignedTasksQuery = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
            ->with(['client', 'job', 'service'])
            ->whereNotNull('start')
            ->where('start', '<=', $endDate) 
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate));
        
        // Personal (Non-Client) Tasks
        $personalTasksQuery = Task::where('staff_id', $staffId)->whereNull('job_id')
            ->whereNotNull('start')
            ->where('start', '<=', $endDate)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate));

        if ($search) {
            $assignedTasksQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
            $personalTasksQuery->when($search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"));
        }
        
        $taskInstances = new Collection();
        foreach ($assignedTasksQuery->get() as $task) {
            $this->expandAndFilterTask($task, $startDate, $endDate, $taskInstances, $statuses);
        }

        $personalTasks = new Collection();
        foreach($personalTasksQuery->get() as $task) {
            $this->expandAndFilterTask($task, $startDate, $endDate, $personalTasks, $statuses, true);
        }

        return [$taskInstances, $personalTasks];
    }

    /**
     * Expands a task into individual instances for a given date range and filters by status.
     */
    private function expandAndFilterTask($task, $startDate, $endDate, &$collection, $statuses, $isPersonal = false)
    {
        $task->is_personal = $isPersonal;
        $completedDates = (array) ($task->completed_at_dates ?? []);

        // Handle non-recurring tasks
        if (!$task->is_recurring) {
            if ($task->start && $task->start->between($startDate, $endDate)) {
                $instanceStatus = $task->status;
                if (empty($statuses) || in_array($instanceStatus, $statuses)) {
                    $task->due_date_instance = $task->start;
                    $collection->push($task);
                }
            }
            return;
        }
        
        // Handle recurring tasks
        if ($task->is_recurring && $task->start && $task->recurring_frequency) {
            $cursor = $task->start->copy();
            $seriesEndDate = $task->end;

            // Fast-forward cursor to the start of the viewing window if it starts before
            while ($cursor->lt($startDate)) {
                if ($seriesEndDate && $cursor->gt($seriesEndDate)) break;
                switch ($task->recurring_frequency) {
                    case 'daily': $cursor->addDay(); break;
                    case 'weekly': $cursor->addWeek(); break;
                    case 'monthly': $cursor->addMonthWithNoOverflow(); break;
                    case 'yearly': $cursor->addYearWithNoOverflow(); break;
                    default: break 2;
                }
            }

            // Generate and filter instances within the viewing window
            while ($cursor->lte($endDate)) {
                if ($seriesEndDate && $cursor->gt($seriesEndDate)) break;
                
                $instanceStatus = in_array($cursor->toDateString(), $completedDates) ? 'completed' : $task->status;
                
                if (empty($statuses) || in_array($instanceStatus, $statuses)) {
                    $instance = clone $task;
                    $instance->status = $instanceStatus; // Override the status for this instance
                    $instance->due_date_instance = $cursor->copy();
                    $collection->push($instance);
                }

                switch ($task->recurring_frequency) {
                    case 'daily': $cursor->addDay(); break;
                    case 'weekly': $cursor->addWeek(); break;
                    case 'monthly': $cursor->addMonthWithNoOverflow(); break;
                    case 'yearly': $cursor->addYearWithNoOverflow(); break;
                    default: break 2;
                }
            }
        }
    }
    
    /**
     * Prepares a flat, sorted collection of all tasks for the 'Time View'.
     */
    private function prepareTimeViewTasks($taskInstances, $personalTasks)
    {
        $allTasks = new Collection($taskInstances);
        
        $personalTasks->each(function ($task) {
            if (!isset($task->due_date_instance)) {
                $task->due_date_instance = $task->start;
            }
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
                if (!in_array($instanceDate, $completedDates)) {
                    $completedDates[] = $instanceDate;
                }
            } else {
                $completedDates = array_filter($completedDates, fn($date) => $date !== $instanceDate);
                $task->status = $newStatus;
            }
            
            $task->completed_at_dates = array_values(array_unique($completedDates));
        } else {
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
                return;
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