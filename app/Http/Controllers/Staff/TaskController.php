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
        
        $statuses = $request->input('statuses');

        if ($statuses === null) {
            $statuses = ['to_do', 'ongoing'];
        }
        if (!is_array($statuses)) {
            $statuses = [];
        }

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

        [$taskInstances, $personalTasks] = $this->getTaskInstancesInDateRange($staffId, $startDate, $endDate, $search, $statuses);

        $allStatuses = ['to_do' => 'To Do', 'ongoing' => 'Ongoing', 'completed' => 'Completed'];
        
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
        
        $clientTaskGroups = $taskInstances->sortBy('due_date_instance')->groupBy(['client.name', 'service.name', 'job.name'], true);
        $years = range(now()->year - 4, now()->year + 2);
        $months = [ 'all' => 'All Months', 1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
        
        return view('Staff.tasks.index', compact('clientTaskGroups', 'personalTasks', 'allStatuses', 'startDate', 'endDate', 'years', 'months'));
    }

    private function getTaskInstancesInDateRange($staffId, Carbon $startDate, Carbon $endDate, $search, $statuses)
    {
        $assignedTasksQuery = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
            ->with(['client', 'job', 'service', 'staff'])
            ->whereNotNull('start')
            ->where('start', '<=', $endDate) 
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate));
        
        $personalTasksQuery = Task::where('staff_id', $staffId)->whereNull('job_id')
            ->whereNotNull('start')
            ->where('start', '<=', $endDate)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate));

        if ($search) {
            $assignedTasksQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('job', fn($jq) => $jq->where('name', 'like', "%{$search}%"));
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

    private function expandAndFilterTask($task, $startDate, $endDate, &$collection, $statuses, $isPersonal = false)
    {
        $task->is_personal = $isPersonal;
        $completedDates = (array) ($task->completed_at_dates ?? []);

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
        
        if ($task->is_recurring && $task->start && $task->recurring_frequency) {
            $cursor = $task->start->copy();
            $seriesEndDate = $task->end;

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

            while ($cursor->lte($endDate)) {
                if ($seriesEndDate && $cursor->gt($seriesEndDate)) break;
                
                $instanceStatus = in_array($cursor->toDateString(), $completedDates) ? 'completed' : $task->status;
                
                if (empty($statuses) || in_array($instanceStatus, $statuses)) {
                    $instance = clone $task;
                    $instance->status = $instanceStatus;
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

    // --- THIS IS THE DEFINITIVE FIX ---
    private function getTask($taskId)
    {
        if (empty($taskId) || !str_contains($taskId, '_')) return null;

        $parts = explode('_', $taskId);
        $type = $parts[0];
        $id = $parts[1] ?? null;

        if ($type === 'p' && $id) {
            return Task::where('id', $id)->where('staff_id', Auth::id())->first();
        }

        if ($type === 'a' && $id) {
            return AssignedTask::where('id', $id)->whereHas('staff', fn($q) => $q->where('users.id', Auth::id()))->first();
        }

        return null;
    }

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
        
        if ($task instanceof AssignedTask) {
            $staffPivot = $task->staff()->where('users.id', Auth::id())->first()->pivot;
            if ($staffPivot->timer_started_at) {
                return response()->json(['error' => 'Please stop the timer before changing the status.'], 422);
            }
        } elseif ($task->timer_started_at) {
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

    public function startTimer(Request $request, $taskId)
    {
        $task = $this->getTask($taskId);
        if (!$task) { return response()->json(['error' => 'Task not found.'], 404); }
        if ($task->status !== 'ongoing') { return response()->json(['error' => 'Task must be "ongoing" to start the timer.'], 422); }
    
        $this->stopAllRunningTimersForUser(Auth::id(), $taskId);
    
        $duration = 0;
        $startedAt = now();
    
        if ($task instanceof AssignedTask) {
            $staffPivot = $task->staff()->where('users.id', Auth::id())->first()->pivot;
            $staffPivot->timer_started_at = $startedAt;
            $staffPivot->save();
            $duration = $staffPivot->duration_in_seconds;
        } else { // Personal Task
            $task->timer_started_at = $startedAt;
            $task->save();
            $duration = $task->duration_in_seconds;
        }
    
        return response()->json([
            'success' => 'Timer started successfully.',
            'timer_started_at' => $startedAt->toIso8601String(),
            'duration_in_seconds' => $duration,
        ]);
    }

    public function stopTimer(Request $request, $taskId)
    {
        $task = $this->getTask($taskId);
        if (!$task) { return response()->json(['error' => 'Task not found.'], 400); }
    
        $newDurationForStaff = 0;
    
        DB::transaction(function () use ($task, &$newDurationForStaff) {
            if ($task instanceof AssignedTask) {
                $staffPivot = $task->staff()->where('users.id', Auth::id())->first()->pivot;
                if (!$staffPivot->timer_started_at) {
                    throw new \Exception('Timer is not running for this task.');
                }
    
                $startTime = Carbon::parse($staffPivot->timer_started_at)->getTimestamp();
                $elapsed = now()->getTimestamp() - $startTime;
    
                $newStaffDuration = ($staffPivot->duration_in_seconds ?? 0) + ($elapsed > 0 ? $elapsed : 0);
    
                $task->staff()->updateExistingPivot(Auth::id(), [
                    'duration_in_seconds' => $newStaffDuration,
                    'timer_started_at' => null,
                ]);
    
                $totalDuration = DB::table('assigned_task_staff')
                    ->where('assigned_task_id', $task->id)
                    ->sum('duration_in_seconds');
    
                $task->duration_in_seconds = $totalDuration;
                $task->save();
                
                $newDurationForStaff = $newStaffDuration;
            } else { // Personal Task
                if (!$task->timer_started_at) {
                    throw new \Exception('Timer is not running for this task.');
                }
    
                $startTime = $task->timer_started_at->getTimestamp();
                $elapsed = now()->getTimestamp() - $startTime;
                if ($elapsed > 0) {
                    $task->duration_in_seconds = ($task->duration_in_seconds ?? 0) + $elapsed;
                }
                $task->timer_started_at = null;
                $task->save();
                $newDurationForStaff = $task->duration_in_seconds;
            }
        });
    
        return response()->json([
            'success' => 'Timer stopped successfully.',
            'new_duration' => $newDurationForStaff,
        ]);
    }
    
    
    // --- THIS IS THE DEFINITIVE FIX ---
    private function stopAllRunningTimersForUser($userId, $excludeTaskId)
    {
        $stopTime = now();
        
        // Stop personal task timers
        Task::where('staff_id', $userId)->whereNotNull('timer_started_at')->get()->each(function ($task) use ($stopTime, $excludeTaskId) {
            $currentTaskId = 'p_' . $task->id;
            if (str_starts_with($excludeTaskId, $currentTaskId)) return;

            $elapsed = $stopTime->getTimestamp() - $task->timer_started_at->getTimestamp();
            if ($elapsed > 0) {
                $task->duration_in_seconds += $elapsed;
            }
            $task->timer_started_at = null;
            $task->save();
        });

        // Stop assigned task timers (on the pivot table)
        $runningPivots = DB::table('assigned_task_staff')
            ->where('user_id', $userId)
            ->whereNotNull('timer_started_at')
            ->get();

        foreach ($runningPivots as $pivot) {
            $currentTaskId = 'a_' . $pivot->assigned_task_id;
            if (str_starts_with($excludeTaskId, $currentTaskId)) continue;

            $startTime = Carbon::parse($pivot->timer_started_at);
            $elapsed = $stopTime->getTimestamp() - $startTime->getTimestamp();

            if ($elapsed > 0) {
                DB::table('assigned_task_staff')
                    ->where('assigned_task_id', $pivot->assigned_task_id)
                    ->where('user_id', $userId)
                    ->update([
                        'duration_in_seconds' => DB::raw("duration_in_seconds + {$elapsed}"),
                        'timer_started_at' => null
                    ]);
                
                // Update the total on the parent task
                $totalDuration = DB::table('assigned_task_staff')->where('assigned_task_id', $pivot->assigned_task_id)->sum('duration_in_seconds');
                AssignedTask::where('id', $pivot->assigned_task_id)->update(['duration_in_seconds' => $totalDuration]);
            }
        }
    }

    public function addManualTime(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'hours' => 'required|integer|min:0',
            'minutes' => 'required|integer|min:0|max:59',
        ]);
        if ($validator->fails()) { return response()->json(['error' => 'Invalid time format.'], 422); }
    
        $task = $this->getTask($taskId);
        if (!$task) { return response()->json(['error' => 'Task not found.'], 404); }
    
        $secondsToAdd = ($request->input('hours') * 3600) + ($request->input('minutes') * 60);
        if ($secondsToAdd <= 0) { return response()->json(['error' => 'Please enter a positive amount of time.'], 422); }
    
        $newDurationForStaff = 0;
    
        DB::transaction(function () use ($task, $secondsToAdd, &$newDurationForStaff) {
            if ($task instanceof AssignedTask) {
                $staffPivot = $task->staff()->where('users.id', Auth::id())->first()->pivot;
                if ($staffPivot->timer_started_at) {
                    throw new \Exception('Please stop the live timer before adding manual time.');
                }
    
                $newStaffDuration = ($staffPivot->duration_in_seconds ?? 0) + $secondsToAdd;
    
                $task->staff()->updateExistingPivot(Auth::id(), [
                    'duration_in_seconds' => $newStaffDuration,
                ]);
    
                $totalDuration = DB::table('assigned_task_staff')
                    ->where('assigned_task_id', $task->id)
                    ->sum('duration_in_seconds');
    
                $task->duration_in_seconds = $totalDuration;
                $task->save();
    
                $newDurationForStaff = $newStaffDuration;
            } else { // Personal Task
                if ($task->timer_started_at) {
                    throw new \Exception('Please stop the live timer before adding manual time.');
                }
                $task->duration_in_seconds = ($task->duration_in_seconds ?? 0) + $secondsToAdd;
                $task->save();
                $newDurationForStaff = $task->duration_in_seconds;
            }
        });
    
        return response()->json([
            'success' => 'Manual time added successfully.',
            'new_duration' => $newDurationForStaff,
        ]);
    }
}