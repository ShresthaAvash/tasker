<?php

namespace App\Http\Controllers\Organization;

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

    private function getTaskInstances($staffId, Carbon $startDate, Carbon $endDate, $search)
    {
        // Assigned (Client) Tasks
        $assignedTasksQuery = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
            ->with(['client', 'job', 'service'])
            ->where(function ($query) {
                $query->where('is_recurring', false)->where('status', '!=', 'completed')
                      ->orWhere('is_recurring', true);
            })
            ->where('start', '<=', $endDate)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate));
        
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

        // Personal Tasks
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

    private function expandRecurringTask($task, $startDate, $endDate, &$collection, $isPersonal = false)
    {
        $task->is_personal = $isPersonal;

        if ($task->is_recurring && $task->start && $task->end) {
            $completedDates = (array) ($task->completed_at_dates ?? []);
            $cursor = $task->start->copy();

            while ($cursor->lte($task->end)) {
                if ($cursor->between($startDate, $endDate) && !in_array($cursor->toDateString(), $completedDates)) {
                    $instance = clone $task;
                    $instance->due_date_instance = $cursor->copy();
                    $collection->push($instance);
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
        } elseif ($task->start && $task->start->between($startDate, $endDate)) {
            $task->due_date_instance = $task->start;
            $collection->push($task);
        }
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
            }
            
            $task->completed_at_dates = array_values(array_unique($completedDates));
            
            if ($newStatus !== 'completed') {
                 $task->status = $newStatus;
            }
        } else {
            $task->status = $newStatus;
        }

        $task->save();

        return response()->json(['success' => 'Status updated successfully!']);
    }
}