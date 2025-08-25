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
    /**
     * Display the time tracking report for the organization by Client.
     */
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

        if (!empty($statuses)) {
            $tasksQuery->whereIn('status', $statuses);
        }

        // Apply date filtering based on task status
        $tasksQuery->where(function ($query) use ($startDate, $endDate) {
            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where('status', 'completed')->whereBetween('updated_at', [$startDate, $endDate]);
            })
            ->orWhere(function ($q) use ($startDate, $endDate) {
                $q->where('status', 'ongoing')
                  ->where('start', '<=', $endDate)
                  ->where(fn($sub) => $sub->whereNull('end')->orWhere('end', '>=', $startDate));
            });
        });

        $tasks = $tasksQuery->where('duration_in_seconds', '>', 0)->get();
        $groupedTasks = $this->groupTasksByClient($tasks);

        if ($request->ajax()) {
            return view('Organization.reports._client_report_table', ['groupedTasks' => $groupedTasks])->render();
        }

        return view('Organization.reports.time', $this->getCommonViewData(
            ['groupedTasks' => $groupedTasks], $request, $startDate, $endDate
        ));
    }

    /**
     * Display the time tracking report for the organization by Staff.
     */
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
                ->whereHas('staff', function ($q) use ($staffMembers) {
                    $q->whereIn('users.id', $staffMembers->pluck('id'))
                      ->where('assigned_task_staff.duration_in_seconds', '>', 0);
                })
                ->with(['service', 'job', 'staff' => fn($q) => $q->where('assigned_task_staff.duration_in_seconds', '>', 0)->select('users.id', 'users.name')]);

            if (!empty($statuses)) {
                $tasksQuery->whereIn('status', $statuses);
            }

            $tasksQuery->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')->whereBetween('updated_at', [$startDate, $endDate]);
                })
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'ongoing')
                      ->where('start', '<=', $endDate)
                      ->where(fn($sub) => $sub->whereNull('end')->orWhere('end', '>=', $startDate));
                });
            });

            $tasks = $tasksQuery->where('duration_in_seconds', '>', 0)->get();
            $reportData = $this->groupTasksByStaff($tasks, $staffMembers);
        }

        if ($request->ajax()) {
            return view('Organization.reports._staff_report_table', ['reportData' => $reportData])->render();
        }

        return view('Organization.reports.staff', $this->getCommonViewData(
            ['reportData' => $reportData], $request, $startDate, $endDate
        ));
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

    private function filterTasksByDate(Collection $tasks, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        if (!$startDate) return $tasks;
        return $tasks->filter(function ($task) use ($startDate, $endDate) {
            if (!$task->is_recurring) return $task->updated_at->between($startDate, $endDate);
            $completedDates = (array) ($task->completed_at_dates ?? []);
            foreach ($completedDates as $dateString) {
                if (Carbon::parse($dateString)->between($startDate, $endDate)) return true;
            }
            return false;
        });
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
                if ($staffOnTask = $task->staff->find($staff->id)) {
                    $serviceId = $task->service->id ?? 'uncategorized';
                    $jobId = $task->job->id ?? 'uncategorized';
                    $duration = $staffOnTask->pivot->duration_in_seconds;
                    if (!isset($staffServices[$serviceId])) $staffServices[$serviceId] = ['name' => $task->service->name ?? 'Uncategorized', 'jobs' => [], 'total_duration' => 0];
                    if (!isset($staffServices[$serviceId]['jobs'][$jobId])) $staffServices[$serviceId]['jobs'][$jobId] = ['name' => $task->job->name ?? 'Uncategorized', 'tasks' => [], 'total_duration' => 0];
                    $staffServices[$serviceId]['jobs'][$jobId]['tasks'][] = ['name' => $task->name, 'duration' => $duration];
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