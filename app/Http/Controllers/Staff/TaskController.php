<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AssignedTask;
use App\Models\Task;
use App\Models\User;
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
                $startDate = Carbon::create($year, $month, 1)->startOfMonth();
                $endDate = Carbon::create($year, $month, 1)->endOfMonth();
            }
        }

        // --- 2. Data Fetching & Expansion ---
        [$taskInstances, $personalTasks] = $this->getTaskInstances($staffId, $startDate, $endDate, $search);

        $allStatuses = ['to_do' => 'To Do', 'ongoing' => 'Ongoing', 'completed' => 'Completed'];
        
        // --- 3. Prepare Data for Views ---
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
        
        // --- Initial Page Load ---
        $clientTaskGroups = $taskInstances->sortBy('due_date_instance')->groupBy(['client.name', 'service.name', 'job.name'], true);
        $years = range(now()->year - 4, now()->year + 2);
        $months = [ 'all' => 'All Months', 1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
        
        return view('Staff.tasks.index', compact('clientTaskGroups', 'personalTasks', 'allStatuses', 'startDate', 'endDate', 'years', 'months'));
    }

    /**
     * Fetch, filter, and expand all task instances for a given staff member and date range.
     */
    private function getTaskInstances($staffId, Carbon $startDate, Carbon $endDate, $search)
    {
        $assignedTasksQuery = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
            ->with(['client', 'job', 'service'])
            ->where('status', '!=', 'completed')
            ->where('start', '<=', $endDate)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate));
        
        if ($search) {
            $assignedTasksQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('job', fn($jq) => $jq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }
        
        $taskInstances = new Collection();
        foreach ($assignedTasksQuery->get() as $task) {
            if ($task->is_recurring && $task->end) {
                $cursor = $task->start->copy();
                while ($cursor->lte($task->end)) {
                    if ($cursor->between($startDate, $endDate)) {
                        $instance = clone $task;
                        $instance->due_date_instance = $cursor->copy();
                        $taskInstances->push($instance);
                    }
                    if ($cursor > $endDate) break;
                    
                    switch ($task->recurring_frequency) {
                        case 'daily': $cursor->addDay(); break;
                        case 'weekly': $cursor->addWeek(); break;
                        case 'monthly': $cursor->addMonthWithNoOverflow(); break;
                        case 'yearly': $cursor->addYearWithNoOverflow(); break;
                        default: break 2;
                    }
                }
            } elseif ($task->start->between($startDate, $endDate)) {
                $task->due_date_instance = $task->start;
                $taskInstances->push($task);
            }
        }

        $personalTasks = Task::where('staff_id', $staffId)->whereNull('job_id')
            ->where('status', '!=', 'completed')
            ->whereBetween('start', [$startDate, $endDate])
            ->when($search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->get();

        return [$taskInstances, $personalTasks];
    }
    
    private function prepareTimeViewTasks($taskInstances, $personalTasks)
    {
        $personalTasks->each(function ($task) {
            $task->due_date_instance = $task->start;
            $task->is_personal = true;
        });
        
        return $taskInstances->concat($personalTasks)->sortBy('due_date_instance');
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
    
    private function stopAndSaveDuration($task)
    {
        if ($task && $task->timer_started_at) {
            $started = Carbon::parse($task->timer_started_at);
            $currentDuration = $task->duration_in_seconds ?? 0;
            $task->duration_in_seconds = $currentDuration + now()->diffInSeconds($started);
            $task->timer_started_at = null;
            $task->save();
        }
    }

    public function updateStatus(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), ['status' => 'required|in:to_do,ongoing,completed']);
        if ($validator->fails()) { return response()->json(['error' => 'Invalid status provided.'], 422); }

        $task = $this->getTask($taskId);
        if ($task) {
            $task->status = $request->status;
            if ($task->status !== 'ongoing' && $task->timer_started_at) {
                $this->stopAndSaveDuration($task);
            }
            $task->save();
            return response()->json(['success' => 'Status updated successfully!']);
        }
        return response()->json(['error' => 'Task not found or you are not authorized.'], 404);
    }
    
    public function startTimer(Request $request, $taskId)
    {
        $staffId = Auth::id();
        
        try {
            DB::transaction(function () use ($staffId, $taskId) {
                $runningPersonal = Task::where('staff_id', $staffId)->whereNotNull('timer_started_at')->first();
                if ($runningPersonal) { $this->stopAndSaveDuration($runningPersonal); }

                $runningAssigned = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))->whereNotNull('timer_started_at')->first();
                if ($runningAssigned) { $this->stopAndSaveDuration($runningAssigned); }
                
                $taskToStart = $this->getTask($taskId);
                if (!$taskToStart) { throw new \Exception('Task not found or you are not authorized.'); }

                $taskToStart->timer_started_at = now();
                $taskToStart->save();
            });
        } catch (\Exception $e) {
            Log::error('Timer start failed: ' . $e->getMessage());
            return response()->json(['error' => 'A database error occurred while starting the timer.'], 500);
        }
        
        $task = $this->getTask($taskId);
        return response()->json([
            'success' => 'Timer started.',
            'task_id' => $taskId,
            'task_name' => $task instanceof AssignedTask ? (optional($task->client)->name . ': ' . $task->name) : $task->name,
            'duration_in_seconds' => $task->duration_in_seconds ?? 0,
            'timer_started_at' => $task->timer_started_at->toIso8601String(),
        ]);
    }

    public function stopTimer(Request $request, $taskId)
    {
        try {
            $task = $this->getTask($taskId);
            if (!$task || !$task->timer_started_at) {
                return response()->json(['error' => 'Timer is not currently running for this task.'], 400);
            }

            $this->stopAndSaveDuration($task);

            return response()->json([
                'success' => 'Timer stopped successfully.',
                'new_duration' => $task->duration_in_seconds
            ]);

        } catch (\Exception $e) {
            Log::error("Stop Timer Failed: " . $e->getMessage());
            return response()->json(['error' => 'An unexpected server error occurred.'], 500);
        }
    }

    public function addManualTime(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), ['hours' => 'required|integer|min:0', 'minutes' => 'required|integer|min:0|max:59']);
        if ($validator->fails()) { return response()->json(['errors' => $validator->errors()], 422); }

        $task = $this->getTask($taskId);
        if (!$task) { return response()->json(['error' => 'Task not found.'], 404); }

        $secondsToAdd = ($request->hours * 3600) + ($request->minutes * 60);
        $task->duration_in_seconds = ($task->duration_in_seconds ?? 0) + $secondsToAdd;
        $task->save();

        return response()->json(['success' => 'Manual time added.', 'new_duration' => $task->duration_in_seconds]);
    }
}