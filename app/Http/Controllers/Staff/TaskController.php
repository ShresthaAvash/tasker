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
            $statuses = [];
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
        
        return view('Staff.tasks.index', compact('clientTaskGroups', 'personalTasks', 'allStatuses', 'startDate', 'endDate', 'years', 'months', 'statuses'));
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

    private function expandAndFilterTask($task, $startDate, Carbon $endDate, &$collection, $statuses, $isPersonal = false)
    {
        $task->is_personal = $isPersonal;

        if (!$task->is_recurring) {
            if ($task->start && $task->start->between($startDate, $endDate)) {
                if (empty($statuses) || in_array($task->status, $statuses)) {
                    $task->due_date_instance = $task->start;
                    $collection->push($task);
                }
            }
            return;
        }
        
        if ($task->is_recurring && $task->start && $task->recurring_frequency) {
            $instanceData = (array) ($task->completed_at_dates ?? []);
            $overrides = (array) ($task->color_overrides ?? []);
            $timerInstanceDate = $overrides['_timer']['user_' . Auth::id()] ?? null;

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
                
                $instanceDateString = $cursor->toDateString();
                $instanceSpecifics = $instanceData[$instanceDateString] ?? [];

                $instanceStatus = $instanceSpecifics['status'] ?? $task->status;
                
                if (empty($statuses) || in_array($instanceStatus, $statuses)) {
                    $instance = clone $task;
                    $instance->status = $instanceStatus;
                    $instance->due_date_instance = $cursor->copy();
                    
                    $userDurations = $instanceSpecifics['durations'] ?? [];
                    $instance->duration_in_seconds = $userDurations['user_' . Auth::id()] ?? 0;

                    if ($timerInstanceDate === $instanceDateString) {
                        if ($instance instanceof AssignedTask) {
                            $instance->timer_started_at = $instance->staff()->where('users.id', Auth::id())->first()->pivot->timer_started_at;
                        } else {
                            $instance->timer_started_at = $task->timer_started_at;
                        }
                    } else {
                        $instance->timer_started_at = null;
                    }

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

    private function getTaskInstance($taskId): ?array
    {
        if (empty($taskId) || !str_contains($taskId, '_')) return null;

        $parts = explode('_', $taskId);
        $type = $parts[0];
        $id = $parts[1] ?? null;
        $instanceDate = count($parts) > 2 ? $parts[2] : null;

        $task = null;
        if ($type === 'p' && $id) {
            $task = Task::where('id', $id)->where('staff_id', Auth::id())->first();
        }

        if ($type === 'a' && $id) {
            $task = AssignedTask::where('id', $id)->whereHas('staff', fn($q) => $q->where('users.id', Auth::id()))->first();
        }
        
        if (!$task) {
            return null;
        }

        return ['task' => $task, 'instance_date' => $instanceDate];
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

        $taskInstance = $this->getTaskInstance($taskId);
        if (!$taskInstance) {
            return response()->json(['error' => 'Task not found or you are not authorized.'], 404);
        }
        $task = $taskInstance['task'];
        $instanceDate = $taskInstance['instance_date'];

        $newStatus = $request->input('status');

        if ($task->is_recurring) {
            if (!$instanceDate) {
                return response()->json(['error' => 'Instance date is required for recurring tasks.'], 422);
            }
            $instanceData = (array) ($task->completed_at_dates ?? []);
            if (!isset($instanceData[$instanceDate])) {
                $instanceData[$instanceDate] = ['status' => $task->status, 'durations' => []];
            }
            $instanceData[$instanceDate]['status'] = $newStatus;
            $task->completed_at_dates = $instanceData;
        } else {
            if ($task instanceof AssignedTask) {
                $staffPivot = $task->staff()->where('users.id', Auth::id())->first()->pivot;
                if ($staffPivot->timer_started_at) {
                    return response()->json(['error' => 'Please stop the timer before changing the status.'], 422);
                }
            } elseif ($task->timer_started_at) {
                return response()->json(['error' => 'Please stop the timer before changing the status.'], 422);
            }
            $task->status = $newStatus;
        }

        $task->save();

        return response()->json(['success' => 'Status updated successfully!']);
    }

    public function startTimer(Request $request, $taskId)
    {
        $taskInstance = $this->getTaskInstance($taskId);
        if (!$taskInstance) { return response()->json(['error' => 'Task not found.'], 404); }
        $task = $taskInstance['task'];
        $instanceDate = $taskInstance['instance_date'];
        
        if ($task->is_recurring) {
            if (!$instanceDate) { return response()->json(['error' => 'Instance date required.'], 422); }
            $instanceData = (array)($task->completed_at_dates ?? []);
            $instanceStatus = $instanceData[$instanceDate]['status'] ?? $task->status;
            if ($instanceStatus !== 'ongoing') {
                return response()->json(['error' => 'Task instance must be "ongoing" to start the timer.'], 422);
            }
        } else {
            if ($task->status !== 'ongoing') { return response()->json(['error' => 'Task must be "ongoing" to start timer.'], 422); }
        }
    
        $this->stopAllRunningTimersForUser(Auth::id(), $taskId);
    
        $startedAt = now();
        $userId = Auth::id();
        $duration = 0;
    
        if ($task->is_recurring) {
            $overrides = (array)($task->color_overrides ?? []);
            if (!isset($overrides['_timer'])) $overrides['_timer'] = [];
            $overrides['_timer']['user_' . $userId] = $instanceDate;
            $task->color_overrides = $overrides;
            
            $instanceData = (array)($task->completed_at_dates ?? []);
            $userDurations = ($instanceData[$instanceDate]['durations'] ?? []);
            $duration = $userDurations['user_' . $userId] ?? 0;
        }

        if ($task instanceof AssignedTask) {
            $task->staff()->updateExistingPivot($userId, ['timer_started_at' => $startedAt]);
            if (!$task->is_recurring) {
                $duration = $task->staff()->where('users.id', $userId)->first()->pivot->duration_in_seconds;
            }
        } else { // Personal Task
            $task->timer_started_at = $startedAt;
            if (!$task->is_recurring) {
                $duration = $task->duration_in_seconds;
            }
        }
        
        $task->save();
    
        return response()->json([
            'success' => 'Timer started successfully.',
            'timer_started_at' => $startedAt->toIso8601String(),
            'duration_in_seconds' => $duration,
        ]);
    }

    public function stopTimer(Request $request, $taskId)
    {
        $taskInstance = $this->getTaskInstance($taskId);
        if (!$taskInstance) { return response()->json(['error' => 'Task not found.'], 404); }
        $task = $taskInstance['task'];
        
        $userId = Auth::id();
        $newDurationForInstance = 0;
    
        DB::transaction(function () use ($task, $userId, &$newDurationForInstance) {
            $overrides = (array)($task->color_overrides ?? []);
            $instanceDate = null;
            if ($task->is_recurring) {
                $instanceDate = $overrides['_timer']['user_' . $userId] ?? null;
            }

            if ($task instanceof AssignedTask) {
                $pivot = $task->staff()->where('users.id', $userId)->first()->pivot;
                if (!$pivot->timer_started_at) { throw new \Exception('Timer not running for this assigned task.'); }
    
                $elapsed = now()->getTimestamp() - Carbon::parse($pivot->timer_started_at)->getTimestamp();
                $elapsed = $elapsed > 0 ? $elapsed : 0;
    
                if ($task->is_recurring && $instanceDate) {
                    $instanceData = (array)($task->completed_at_dates ?? []);
                    if (!isset($instanceData[$instanceDate])) $instanceData[$instanceDate] = ['status' => $task->status, 'durations' => []];
                    if (!isset($instanceData[$instanceDate]['durations'])) $instanceData[$instanceDate]['durations'] = [];
                    $currentDuration = $instanceData[$instanceDate]['durations']['user_' . $userId] ?? 0;
                    $newDurationForInstance = $currentDuration + $elapsed;
                    $instanceData[$instanceDate]['durations']['user_' . $userId] = $newDurationForInstance;
                    $task->completed_at_dates = $instanceData;
                } else {
                    $newDurationForInstance = ($pivot->duration_in_seconds ?? 0) + $elapsed;
                }

                $task->staff()->updateExistingPivot($userId, [
                    'duration_in_seconds' => $task->is_recurring ? $pivot->duration_in_seconds + $elapsed : $newDurationForInstance,
                    'timer_started_at' => null
                ]);
                $task->increment('duration_in_seconds', $elapsed);

            } else { // Personal Task
                if (!$task->timer_started_at) { throw new \Exception('Timer not running for this personal task.'); }
    
                $elapsed = now()->getTimestamp() - $task->timer_started_at->getTimestamp();
                $elapsed = $elapsed > 0 ? $elapsed : 0;
    
                if ($task->is_recurring && $instanceDate) {
                    $instanceData = (array)($task->completed_at_dates ?? []);
                    if (!isset($instanceData[$instanceDate])) $instanceData[$instanceDate] = ['status' => $task->status, 'durations' => []];
                    if (!isset($instanceData[$instanceDate]['durations'])) $instanceData[$instanceDate]['durations'] = [];
                    $currentDuration = $instanceData[$instanceDate]['durations']['user_' . $userId] ?? 0;
                    $newDurationForInstance = $currentDuration + $elapsed;
                    $instanceData[$instanceDate]['durations']['user_' . $userId] = $newDurationForInstance;
                    $task->completed_at_dates = $instanceData;
                } else {
                    $newDurationForInstance = ($task->duration_in_seconds ?? 0) + $elapsed;
                }
                $task->duration_in_seconds += $elapsed;
                $task->timer_started_at = null;
            }
    
            if ($task->is_recurring) {
                unset($overrides['_timer']['user_' . $userId]);
                if (empty($overrides['_timer'])) unset($overrides['_timer']);
                $task->color_overrides = $overrides;
            }
            
            $task->save();
        });
    
        return response()->json([
            'success' => 'Timer stopped successfully.',
            'new_duration' => $newDurationForInstance,
        ]);
    }

    private function stopAllRunningTimersForUser($userId, $excludeTaskId)
    {
        $stopTime = now();
        
        Task::where('staff_id', $userId)->whereNotNull('timer_started_at')->get()->each(function ($task) use ($stopTime, $excludeTaskId, $userId) {
            $overrides = (array)($task->color_overrides ?? []);
            $instanceDate = $task->is_recurring ? ($overrides['_timer']['user_' . $userId] ?? null) : null;
            $currentTaskId = 'p_' . $task->id . ($instanceDate ? '_' . $instanceDate : '');

            if ($currentTaskId === $excludeTaskId) return;

            $elapsed = $stopTime->getTimestamp() - $task->timer_started_at->getTimestamp();
            if ($elapsed > 0) {
                if ($task->is_recurring && $instanceDate) {
                    $instanceData = (array)($task->completed_at_dates ?? []);
                    if (!isset($instanceData[$instanceDate])) $instanceData[$instanceDate] = ['status' => $task->status, 'durations' => []];
                    if (!isset($instanceData[$instanceDate]['durations'])) $instanceData[$instanceDate]['durations'] = [];
                    $instanceData[$instanceDate]['durations']['user_' . $userId] = ($instanceData[$instanceDate]['durations']['user_' . $userId] ?? 0) + $elapsed;
                    $task->completed_at_dates = $instanceData;
                    unset($overrides['_timer']['user_' . $userId]);
                    $task->color_overrides = $overrides;
                }
                $task->duration_in_seconds += $elapsed;
            }
            $task->timer_started_at = null;
            $task->save();
        });

        $runningPivots = DB::table('assigned_task_staff')->where('user_id', $userId)->whereNotNull('timer_started_at')->get();
        foreach ($runningPivots as $pivot) {
            $task = AssignedTask::find($pivot->assigned_task_id);
            if (!$task) continue;

            $overrides = (array)($task->color_overrides ?? []);
            $instanceDate = $task->is_recurring ? ($overrides['_timer']['user_' . $userId] ?? null) : null;
            $currentTaskId = 'a_' . $task->id . ($instanceDate ? '_' . $instanceDate : '');
            
            if ($currentTaskId === $excludeTaskId) continue;

            $startTime = Carbon::parse($pivot->timer_started_at);
            $elapsed = $stopTime->getTimestamp() - $startTime->getTimestamp();

            if ($elapsed > 0) {
                 if ($task->is_recurring && $instanceDate) {
                    $instanceData = (array)($task->completed_at_dates ?? []);
                    if (!isset($instanceData[$instanceDate])) $instanceData[$instanceDate] = ['status' => $task->status, 'durations' => []];
                    if (!isset($instanceData[$instanceDate]['durations'])) $instanceData[$instanceDate]['durations'] = [];
                    $instanceData[$instanceDate]['durations']['user_' . $userId] = ($instanceData[$instanceDate]['durations']['user_' . $userId] ?? 0) + $elapsed;
                    $task->completed_at_dates = $instanceData;
                    unset($overrides['_timer']['user_' . $userId]);
                    $task->color_overrides = $overrides;
                }
                DB::table('assigned_task_staff')->where('assigned_task_id', $pivot->assigned_task_id)->where('user_id', $userId)->increment('duration_in_seconds', $elapsed);
                $task->increment('duration_in_seconds', $elapsed);
                $task->save();
            }
            
            DB::table('assigned_task_staff')->where('assigned_task_id', $pivot->assigned_task_id)->where('user_id', $userId)->update(['timer_started_at' => null]);
        }
    }

    public function addManualTime(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), ['hours' => 'required|integer|min:0', 'minutes' => 'required|integer|min:0|max:59']);
        if ($validator->fails()) { return response()->json(['error' => 'Invalid time format.'], 422); }
    
        $taskInstance = $this->getTaskInstance($taskId);
        if (!$taskInstance) { return response()->json(['error' => 'Task not found.'], 404); }
        $task = $taskInstance['task'];
        $instanceDate = $taskInstance['instance_date'];
        
        $secondsToAdd = ($request->input('hours') * 3600) + ($request->input('minutes') * 60);
        if ($secondsToAdd <= 0) { return response()->json(['error' => 'Please enter a positive amount of time.'], 422); }
    
        $newDurationForInstance = 0;
        $userId = Auth::id();

        DB::transaction(function () use ($task, $secondsToAdd, $userId, $instanceDate, &$newDurationForInstance) {
            if ($task instanceof AssignedTask) {
                if ($task->staff()->where('users.id', $userId)->first()->pivot->timer_started_at) {
                    throw new \Exception('Please stop the live timer before adding manual time.');
                }
                if ($task->is_recurring && $instanceDate) {
                    $instanceData = (array)($task->completed_at_dates ?? []);
                    if (!isset($instanceData[$instanceDate])) $instanceData[$instanceDate] = ['status' => $task->status, 'durations' => []];
                    if (!isset($instanceData[$instanceDate]['durations'])) $instanceData[$instanceDate]['durations'] = [];
                    $newDurationForInstance = ($instanceData[$instanceDate]['durations']['user_' . $userId] ?? 0) + $secondsToAdd;
                    $instanceData[$instanceDate]['durations']['user_' . $userId] = $newDurationForInstance;
                    $task->completed_at_dates = $instanceData;
                } else {
                    $pivot = $task->staff()->where('users.id', $userId)->first()->pivot;
                    $newDurationForInstance = ($pivot->duration_in_seconds ?? 0) + $secondsToAdd;
                }
                $task->staff()->where('users.id', $userId)->increment('duration_in_seconds', $secondsToAdd);
                $task->increment('duration_in_seconds', $secondsToAdd);
            } else { // Personal Task
                if ($task->timer_started_at) { throw new \Exception('Please stop the live timer before adding manual time.'); }

                if ($task->is_recurring && $instanceDate) {
                    $instanceData = (array)($task->completed_at_dates ?? []);
                    if (!isset($instanceData[$instanceDate])) $instanceData[$instanceDate] = ['status' => $task->status, 'durations' => []];
                    if (!isset($instanceData[$instanceDate]['durations'])) $instanceData[$instanceDate]['durations'] = [];
                    $newDurationForInstance = ($instanceData[$instanceDate]['durations']['user_' . $userId] ?? 0) + $secondsToAdd;
                    $instanceData[$instanceDate]['durations']['user_' . $userId] = $newDurationForInstance;
                    $task->completed_at_dates = $instanceData;
                } else {
                    $newDurationForInstance = ($task->duration_in_seconds ?? 0) + $secondsToAdd;
                }
                $task->duration_in_seconds += $secondsToAdd;
            }
            $task->save();
        });
    
        return response()->json([ 'success' => 'Manual time added successfully.', 'new_duration' => $newDurationForInstance ]);
    }
}