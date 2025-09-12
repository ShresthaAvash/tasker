<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AssignedTask;
use App\Models\User;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportController extends Controller
{
    public function timeReport(Request $request)
    {
        $organizationId = Auth::id();
        $search = $request->input('search');
        [$startDate, $endDate] = $this->resolveDatesFromRequest($request);
        $statuses = $request->input('statuses', []);

        $tasksQuery = AssignedTask::query()
            ->whereHas('client', fn($q) => $q->where('organization_id', $organizationId))
            ->whereNotNull('start')
            ->where('start', '<=', $endDate)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate))
            ->with(['client', 'service', 'staff']);

        if ($search) {
            $tasksQuery->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%') // Search task name
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }
        
        $tasksQuery->where(function ($query) use ($statuses) {
            $query->where('is_recurring', true)
                  ->orWhere(function ($q) use ($statuses) {
                      $q->where('is_recurring', false)
                        ->when(!empty($statuses), function ($sq) use ($statuses) {
                            $sq->whereIn('status', $statuses);
                        });
                  });
        });

        $tasks = $tasksQuery->get();
        $expandedTasks = $this->expandTasksForReport($tasks, $startDate, $endDate, $statuses);
        $groupedTasks = $this->groupTasksByClient($expandedTasks);

        if ($request->ajax()) {
            return view('Organization.reports._client_report_table', ['groupedTasks' => $groupedTasks])->render();
        }

        return view('Organization.reports.time', $this->getCommonViewData(
            ['groupedTasks' => $groupedTasks], $request, $startDate, $endDate
        ));
    }

    public function staffReport(Request $request)
    {
        $organizationId = Auth::id();
        $search = $request->input('search');
        [$startDate, $endDate] = $this->resolveDatesFromRequest($request);
        $statuses = $request->input('statuses', []);

        $allStaffMembers = User::where('organization_id', $organizationId)
                           ->where('type', 'T')
                           ->orderBy('name')
                           ->get()
                           ->keyBy('id');

        $reportData = collect();

        if ($allStaffMembers->isNotEmpty()) {
            $tasksQuery = AssignedTask::query()
                ->whereHas('staff', fn($q) => $q->whereIn('users.id', $allStaffMembers->pluck('id')))
                ->whereNotNull('start')
                ->where('start', '<=', $endDate)
                ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate))
                ->with(['client', 'service', 'staff' => fn($q) => $q->select('users.id', 'users.name')]);

            if ($search) {
                $tasksQuery->where(function($q) use ($search) {
                    $q->whereHas('staff', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                      ->orWhere('name', 'like', '%' . $search . '%')
                      ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                      ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
                });
            }
            
            $tasksQuery->where(function ($query) use ($statuses) {
                $query->where('is_recurring', true)
                    ->orWhere(function ($q) use ($statuses) {
                        $q->where('is_recurring', false)
                            ->when(!empty($statuses), function ($sq) use ($statuses) {
                                $sq->whereIn('status', $statuses);
                            });
                    });
            });

            $tasks = $tasksQuery->get();
            $expandedTasks = $this->expandTasksForReport($tasks, $startDate, $endDate, $statuses);
            $reportData = $this->groupTasksByStaff($expandedTasks, $allStaffMembers);
        }

        if ($request->ajax()) {
            return view('Organization.reports._staff_report_table', ['reportData' => $reportData])->render();
        }

        return view('Organization.reports.staff', $this->getCommonViewData(
            ['reportData' => $reportData], $request, $startDate, $endDate
        ));
    }

    public function individualStaffReport(Request $request, User $staff)
    {
        $organizationId = Auth::user()->type === 'O' ? Auth::id() : Auth::user()->organization_id;
        if ($staff->organization_id !== $organizationId) {
            throw new AuthorizationException('You are not authorized to view this staff member\'s report.');
        }

        [$startDate, $endDate] = $this->resolveDatesFromRequest($request);
        $clientIds = $request->input('clients', []);
        $serviceIds = $request->input('services', []);
        $statuses = $request->input('statuses', []);
        $search = $request->input('search');

        $tasksQuery = AssignedTask::query()
            ->whereHas('staff', fn($q) => $q->where('users.id', $staff->id))
            ->whereNotNull('start')
            ->where('start', '<=', $endDate)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate))
            ->with(['client', 'service']);

        if ($search) {
            $tasksQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }

        if (!empty($clientIds)) $tasksQuery->whereIn('client_id', $clientIds);
        if (!empty($serviceIds)) $tasksQuery->whereIn('service_id', $serviceIds);

        $baseTasks = $tasksQuery->get();
        $taskInstances = $this->expandAndCalculateStaffTasks($baseTasks, $staff->id, $startDate, $endDate, $statuses);

        $sort_by = $request->get('sort_by', 'due_date');
        $sort_order = $request->get('sort_order', 'asc');

        if ($sort_order === 'desc') {
            $taskInstances = $taskInstances->sortByDesc($sort_by);
        } else {
            $taskInstances = $taskInstances->sortBy($sort_by);
        }

        $perPage = 15;
        $currentPage = $request->input('page', 1);
        $currentPageItems = $taskInstances->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedTaskInstances = new LengthAwarePaginator($currentPageItems, $taskInstances->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        if ($request->ajax()) {
            return view('Organization.reports._individual_staff_report_table', ['taskInstances' => $paginatedTaskInstances, 'sort_by' => $sort_by, 'sort_order' => $sort_order])->render();
        }

        $clients = User::where('organization_id', $organizationId)->where('type', 'C')->orderBy('name')->get();
        $services = Service::where('organization_id', $organizationId)->orderBy('name')->get();

        return view('Organization.reports.individual_staff', $this->getCommonViewData(
            ['staff' => $staff, 'taskInstances' => $paginatedTaskInstances, 'clients' => $clients, 'services' => $services, 'sort_by' => $sort_by, 'sort_order' => $sort_order],
            $request, $startDate, $endDate
        ));
    }

    public function individualClientReport(Request $request, User $client)
    {
        $organizationId = Auth::user()->type === 'O' ? Auth::id() : Auth::user()->organization_id;
        if ($client->organization_id !== $organizationId) {
            throw new AuthorizationException('You are not authorized to view this client\'s report.');
        }
    
        [$startDate, $endDate] = $this->resolveDatesFromRequest($request);
        $staffIds = $request->input('staff', []);
        $serviceIds = $request->input('services', []);
        $statuses = $request->input('statuses', []);
        $search = $request->input('search');
    
        $tasksQuery = AssignedTask::query()
            ->where('client_id', $client->id)
            ->whereNotNull('start')
            ->where('start', '<=', $endDate)
            ->where(fn($q) => $q->whereNull('end')->orWhere('end', '>=', $startDate))
            ->with(['service', 'staff']);
    
        if ($search) {
            $tasksQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }
    
        if (!empty($staffIds)) {
            $tasksQuery->whereHas('staff', fn($q) => $q->whereIn('users.id', $staffIds));
        }
        if (!empty($serviceIds)) {
            $tasksQuery->whereIn('service_id', $serviceIds);
        }
    
        $baseTasks = $tasksQuery->get();
        $taskInstances = $this->expandTasksForReport($baseTasks, $startDate, $endDate, $statuses);
    
        $sort_by = $request->get('sort_by', 'due_date');
        $sort_order = $request->get('sort_order', 'asc');
    
        if ($sort_order === 'desc') {
            $taskInstances = $taskInstances->sortByDesc($sort_by);
        } else {
            $taskInstances = $taskInstances->sortBy($sort_by);
        }
    
        $perPage = 15;
        $currentPage = $request->input('page', 1);
        $currentPageItems = $taskInstances->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedTaskInstances = new LengthAwarePaginator($currentPageItems, $taskInstances->count(), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    
        if ($request->ajax()) {
            return view('Organization.reports._individual_client_report_table', ['taskInstances' => $paginatedTaskInstances, 'sort_by' => $sort_by, 'sort_order' => $sort_order])->render();
        }
    
        $staff = User::where('organization_id', $organizationId)->where('type', 'T')->orderBy('name')->get();
        $services = Service::where('organization_id', $organizationId)->whereHas('clients', fn($q) => $q->where('user_id', $client->id))->orderBy('name')->get();
    
        // --- THIS IS THE FIX ---
        // We now pass the paginated instance to the main view as well.
        return view('Organization.reports.individual_client', $this->getCommonViewData(
            ['client' => $client, 'taskInstances' => $paginatedTaskInstances, 'staff' => $staff, 'services' => $services, 'sort_by' => $sort_by, 'sort_order' => $sort_order],
            $request, $startDate, $endDate
        ));
    }
    
    // --- NEW HELPER METHOD FOR STAFF REPORT ---
    private function expandAndCalculateStaffTasks(Collection $tasks, int $staffId, Carbon $startDate, Carbon $endDate, array $statuses): Collection
    {
        $instances = new Collection();
        $userKey = 'user_' . $staffId;

        foreach ($tasks as $task) {
            if (!$task->is_recurring) {
                if ($task->start && $task->start->between($startDate, $endDate)) {
                    if (empty($statuses) || in_array($task->status, $statuses)) {
                        $instance = clone $task;
                        $instance->due_date = $instance->start;
                        $instance->staff_duration = $instance->staff()->find($staffId)->pivot->duration_in_seconds ?? 0;
                        $instances->push($instance);
                    }
                }
                continue;
            }

            $instanceData = (array)($task->completed_at_dates ?? []);
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
                    $instance->due_date = $cursor->copy();
                    $userDurations = $instanceSpecifics['durations'] ?? [];
                    $instance->staff_duration = $userDurations[$userKey] ?? 0;
                    $instances->push($instance);
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

        return $instances;
    }

    private function expandTasksForReport(Collection $tasks, Carbon $startDate, Carbon $endDate, array $statuses): Collection
    {
        $instances = new Collection();

        foreach ($tasks as $task) {
            $task->due_date = $task->start;
            // Case 1: Non-recurring tasks
            if (!$task->is_recurring) {
                if ($task->start && $task->start->between($startDate, $endDate)) {
                     if (empty($statuses) || in_array($task->status, $statuses)) {
                        $instances->push($task);
                    }
                }
                continue;
            }

            // Case 2: Recurring tasks
            $instanceData = (array)($task->completed_at_dates ?? []);
            $cursor = $task->start->copy();
            $seriesEndDate = $task->end;

            // Efficiently move cursor to the start of the reporting window
            while ($cursor->lt($startDate)) {
                if ($seriesEndDate && $cursor->gt($seriesEndDate)) break;
                switch ($task->recurring_frequency) {
                    case 'daily': $cursor->addDay(); break;
                    case 'weekly': $cursor->addWeek(); break;
                    case 'monthly': $cursor->addMonthWithNoOverflow(); break;
                    case 'yearly': $cursor->addYearWithNoOverflow(); break;
                    default: break 2; // Break out of both loops
                }
            }

            // Generate instances within the reporting window
            while ($cursor->lte($endDate)) {
                if ($seriesEndDate && $cursor->gt($seriesEndDate)) break;

                $instanceDateString = $cursor->toDateString();
                $instanceSpecifics = $instanceData[$instanceDateString] ?? [];
                
                $instanceStatus = $instanceSpecifics['status'] ?? $task->status;

                if (empty($statuses) || in_array($instanceStatus, $statuses)) {
                    $instance = clone $task;
                    $instance->name = $task->name . ' (' . $cursor->format('M j') . ')';
                    $instance->status = $instanceStatus;
                    $instance->due_date = $cursor->copy();
                    
                    $totalDurationForInstance = 0;
                    $clonedStaff = new \Illuminate\Database\Eloquent\Collection();
                    $userDurations = $instanceSpecifics['durations'] ?? [];

                    foreach($task->staff as $staffMember) {
                        $clonedMember = clone $staffMember;
                        $clonedMember->pivot = clone $staffMember->pivot;
                        $duration = $userDurations['user_' . $staffMember->id] ?? 0;
                        $clonedMember->pivot->duration_in_seconds = $duration;
                        $totalDurationForInstance += $duration;
                        $clonedStaff->push($clonedMember);
                    }
                    $instance->setRelation('staff', $clonedStaff);
                    $instance->duration_in_seconds = $totalDurationForInstance;
                    
                    $instances->push($instance);
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
        
        return $instances;
    }
    
    private function resolveDatesFromRequest(Request $request): array
    {
        if ($request->get('use_custom_range') === 'true') {
            $start = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfDay();
            $end = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfDay();
            return [$start, $end];
        }
        
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        if ($month === 'all' || $month === null) {
            return [Carbon::create($year)->startOfYear(), Carbon::create($year)->endOfYear()];
        }
        
        $startDate = Carbon::create($year, (int)$month, 1)->startOfMonth();
        return [$startDate, $startDate->copy()->endOfMonth()];
    }
    
    private function getCommonViewData(array $data, Request $request, Carbon $startDate, Carbon $endDate): array
    {
        $months = ['all' => 'All Months'];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
        }

        return array_merge($data, [
            'search' => $request->input('search'),
            'statuses' => $request->input('statuses', []),
            'years' => range(now()->year - 4, now()->year + 2),
            'months' => $months,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'use_custom_range' => $request->get('use_custom_range') === 'true',
            'currentYear' => $request->get('year', now()->year),
            'currentMonth' => $request->get('month', now()->month),
        ]);
    }

    private function groupTasksByClient(Collection $tasks): Collection
    {
        return $tasks->groupBy('client.name')->map(fn($clientTasks) => 
            $clientTasks->groupBy('service.name')
        );
    }

    private function groupTasksByStaff(Collection $tasks, Collection $staffMembers): Collection
    {
        $reportData = collect();
        foreach ($staffMembers as $staff) {
            $staffServices = []; $staffTotalDuration = 0;
            foreach ($tasks as $task) {
                if ($staffOnTask = $task->staff->firstWhere('id', $staff->id)) {
                    $serviceId = $task->service->id ?? 'uncategorized';
                    $duration = $staffOnTask->pivot->duration_in_seconds;
                    if (!isset($staffServices[$serviceId])) $staffServices[$serviceId] = ['name' => $task->service->name ?? 'Uncategorized', 'tasks' => [], 'total_duration' => 0];
                    $staffServices[$serviceId]['tasks'][] = ['name' => $task->name, 'duration' => $duration, 'status' => $task->status];
                    $staffServices[$serviceId]['total_duration'] += $duration;
                    $staffTotalDuration += $duration;
                }
            }
            if (!empty($staffServices)) {
                $reportData->push((object)['staff_name' => $staff->name, 'services' => $staffServices, 'total_duration' => $staffTotalDuration]);
            }
        }
        return $reportData;
    }
}