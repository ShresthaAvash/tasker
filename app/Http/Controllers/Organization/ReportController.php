<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AssignedTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function timeReport(Request $request)
    {
        $organizationId = Auth::id();
        $search = $request->input('search');
        [$startDate, $endDate] = $this->resolveDatesFromRequest($request);
        $statuses = $request->input('statuses', []);

        $tasksQuery = AssignedTask::query()
            ->whereHas('client', function ($q) use ($organizationId, $search) {
                $q->where('organization_id', $organizationId);
                if ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                }
            })
            ->with(['client', 'service', 'job', 'staff']);

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

        $staffMembersQuery = User::where('organization_id', $organizationId)->where('type', 'T')->orderBy('name');
        if ($search) {
            $staffMembersQuery->where('name', 'like', '%' . $search . '%');
        }
        $staffMembers = $staffMembersQuery->get()->keyBy('id');
        $reportData = collect();

        if ($staffMembers->isNotEmpty()) {
            $tasksQuery = AssignedTask::query()
                ->whereHas('staff', fn($q) => $q->whereIn('users.id', $staffMembers->pluck('id')))
                ->with(['service', 'job', 'staff' => fn($q) => $q->select('users.id', 'users.name')]);

            $tasks = $tasksQuery->get();
            $expandedTasks = $this->expandTasksForReport($tasks, $startDate, $endDate, $statuses);
            $reportData = $this->groupTasksByStaff($expandedTasks, $staffMembers);
        }

        if ($request->ajax()) {
            return view('Organization.reports._staff_report_table', ['reportData' => $reportData])->render();
        }

        return view('Organization.reports.staff', $this->getCommonViewData(
            ['reportData' => $reportData], $request, $startDate, $endDate
        ));
    }

    private function expandTasksForReport(Collection $tasks, Carbon $startDate, Carbon $endDate, array $statuses): Collection
    {
        $instances = new Collection();

        foreach ($tasks as $task) {
            if (!$task->is_recurring) {
                if (empty($statuses) || in_array($task->status, $statuses)) {
                    $instances->push($task);
                }
                continue;
            }

            $instanceData = (array)($task->completed_at_dates ?? []);
            $cursor = $task->start->copy();
            $seriesEndDate = $task->end;

            while ($cursor->lt($startDate)) {
                if ($seriesEndDate && $cursor->gt($seriesEndDate)) break;
                switch ($task->recurring_frequency) {
                    case 'daily': $cursor->addDay(); break; case 'weekly': $cursor->addWeek(); break;
                    case 'monthly': $cursor->addMonthWithNoOverflow(); break; case 'yearly': $cursor->addYearWithNoOverflow(); break;
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
                    
                    $totalDurationForInstance = 0;
                    $clonedStaff = new \Illuminate\Database\Eloquent\Collection(); // Use Eloquent Collection
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
                    case 'daily': $cursor->addDay(); break; case 'weekly': $cursor->addWeek(); break;
                    case 'monthly': $cursor->addMonthWithNoOverflow(); break; case 'yearly': $cursor->addYearWithNoOverflow(); break;
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
            $clientTasks->groupBy('service.name')->map(fn($serviceTasks) => 
                $serviceTasks->groupBy('job.name')
            )
        );
    }

    private function groupTasksByStaff(Collection $tasks, Collection $staffMembers): Collection
    {
        $reportData = collect();
        foreach ($staffMembers as $staff) {
            $staffServices = []; $staffTotalDuration = 0;
            foreach ($tasks as $task) {
                // --- THIS IS THE FIX: Changed find() to firstWhere() ---
                if ($staffOnTask = $task->staff->firstWhere('id', $staff->id)) {
                    $serviceId = $task->service->id ?? 'uncategorized';
                    $jobId = $task->job->id ?? 'uncategorized';
                    $duration = $staffOnTask->pivot->duration_in_seconds;
                    if (!isset($staffServices[$serviceId])) $staffServices[$serviceId] = ['name' => $task->service->name ?? 'Uncategorized', 'jobs' => [], 'total_duration' => 0];
                    if (!isset($staffServices[$serviceId]['jobs'][$jobId])) $staffServices[$serviceId]['jobs'][$jobId] = ['name' => $task->job->name ?? 'Uncategorized', 'tasks' => [], 'total_duration' => 0];
                    $staffServices[$serviceId]['jobs'][$jobId]['tasks'][] = ['name' => $task->name, 'duration' => $duration, 'status' => $task->status];
                    $staffServices[$serviceId]['jobs'][$jobId]['total_duration'] += $duration;
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