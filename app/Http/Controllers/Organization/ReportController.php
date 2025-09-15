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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
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
        
        $groupedTasks = $this->groupTasksForReport($taskInstances);

        if ($request->ajax()) {
            return view('Organization.reports._individual_staff_report_table', compact('groupedTasks'))->render();
        }

        $clients = User::where('organization_id', $organizationId)->where('type', 'C')->orderBy('name')->get();
        $services = Service::where('organization_id', $organizationId)->orderBy('name')->get();

        return view('Organization.reports.individual_staff', $this->getCommonViewData(
            compact('staff', 'groupedTasks', 'clients', 'services'),
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
            ->with(['service.clients', 'staff']); // Eager load service and its client relationship
    
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

        $groupedTasks = $this->groupTasksForReport($taskInstances);
    
        if ($request->ajax()) {
            return view('Organization.reports._individual_client_report_table', compact('groupedTasks', 'client'))->render();
        }
    
        $staff = User::where('organization_id', $organizationId)->where('type', 'T')->orderBy('name')->get();
        $services = Service::where('organization_id', $organizationId)->whereHas('clients', fn($q) => $q->where('user_id', $client->id))->orderBy('name')->get();
    
        return view('Organization.reports.individual_client', $this->getCommonViewData(
            compact('client', 'groupedTasks', 'staff', 'services'),
            $request, $startDate, $endDate
        ));
    }
    
    public function updateServiceStatus(Request $request, User $client, Service $service)
    {
        $organizationId = Auth::user()->organization_id ?? Auth::id();
        if ($service->organization_id !== $organizationId || $client->organization_id !== $organizationId) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        // Update the pivot table
        DB::table('client_service')
            ->where('user_id', $client->id)
            ->where('service_id', $service->id)
            ->update(['status' => $request->input('status')]);

        return response()->json(['success' => 'Service status updated successfully!']);
    }
    
    private function groupTasksForReport(Collection $tasks): Collection
    {
        return $tasks->groupBy(function ($task) {
            return optional($task->service)->name ?? 'Uncategorized';
        })->map(function ($tasksInService) {
            return [
                'service' => $tasksInService->first()->service,
                'tasks' => $tasksInService->sortBy('due_date'),
                'total_duration' => $tasksInService->sum('duration_in_seconds'),
            ];
        });
    }
    
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
                        $instance->duration_in_seconds = $instance->staff_duration; // Standardize for grouping
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
                    $instance->duration_in_seconds = $instance->staff_duration; // Standardize for grouping
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
            if (!$task->is_recurring) {
                if ($task->start && $task->start->between($startDate, $endDate)) {
                     if (empty($statuses) || in_array($task->status, $statuses)) {
                        $instance = clone $task;
                        $instance->status = $task->status; 
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
}