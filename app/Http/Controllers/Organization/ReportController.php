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

        // --- NEW FILTER LOGIC ---
        $useCustomRange = $request->get('use_custom_range') === 'true';
        $statuses = $request->input('statuses');
        if (empty($statuses) || !is_array($statuses)) {
            $statuses = ['ongoing', 'completed']; // Default to both if nothing is selected
        }

        $startDate = null;
        $endDate = null;

        if ($useCustomRange) {
            $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->startOfDay() : now()->startOfMonth();
            $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->endOfDay() : now()->endOfMonth();
        } else {
            $year = $request->get('year', now()->year);
            $month = $request->get('month', 'all');
            if ($month !== 'all' && $month !== null) {
                 $startDate = Carbon::create($year, (int)$month, 1)->startOfMonth();
                 $endDate = Carbon::create($year, (int)$month, 1)->endOfMonth();
            } else {
                 $startDate = Carbon::create($year)->startOfYear();
                 $endDate = Carbon::create($year)->endOfYear();
            }
        }
        // --- END NEW FILTER LOGIC ---

        $tasksQuery = AssignedTask::whereIn('status', $statuses)
            ->whereHas('client', function ($q) use ($organizationId, $search) {
                $q->where('organization_id', $organizationId);
                if ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                }
            })
            ->with(['client', 'service', 'job', 'staff']);

        // Date filtering logic
        if ($startDate && $endDate) {
            $tasksQuery->where(function ($query) use ($startDate, $endDate) {
                // Tasks completed within the period
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'completed')
                    ->whereBetween('updated_at', [$startDate, $endDate]);
                })
                // Or tasks that were ongoing during any part of the period
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'ongoing')
                    ->where('start', '<=', $endDate)
                    ->where(function($subQuery) use ($startDate) {
                        $subQuery->whereNull('end')->orWhere('end', '>=', $startDate);
                    });
                });
            });
        }

        $tasks = $tasksQuery->get();
        $groupedTasks = $this->groupTasksByClient($tasks);

        if ($request->ajax()) {
            return view('Organization.reports._client_report_table', [
                'groupedTasks' => $groupedTasks,
            ])->render();
        }
        
        $years = range(now()->year - 4, now()->year + 2);
        $months = [ 'all' => 'All Months' ];
        foreach(range(1,12) as $month) {
            $months[$month] = Carbon::create(null, $month)->format('F');
        }

        return view('Organization.reports.time', [
            'groupedTasks' => $groupedTasks,
            'search' => $search,
            'years' => $years,
            'months' => $months,
            'currentYear' => now()->year,
            'currentMonth' => now()->month,
        ]);
    }

    /**
     * Display the time tracking report for the organization by Staff.
     */
    public function staffReport(Request $request)
    {
        $organizationId = Auth::id();
        $period = $request->input('period', 'month');
        $search = $request->input('search');

        [$startDate, $endDate] = $this->getDateRangeFromPeriod(
            $period,
            $request->input('start_date'),
            $request->input('end_date')
        );

        $staffMembersQuery = User::where('organization_id', $organizationId)
            ->where('type', 'T')->orderBy('name');

        if ($search) {
            $staffMembersQuery->where('name', 'like', '%' . $search . '%');
        }
        $staffMembers = $staffMembersQuery->get()->keyBy('id');

        if ($staffMembers->isEmpty()) {
            $reportData = collect();
        } else {
            $tasksQuery = AssignedTask::where('status', 'completed')
                ->whereHas('staff', function ($q) use ($staffMembers) {
                    $q->whereIn('users.id', $staffMembers->pluck('id'))
                      ->where('assigned_task_staff.duration_in_seconds', '>', 0);
                })
                ->with(['service', 'job', 'staff' => fn($q) => $q->where('assigned_task_staff.duration_in_seconds', '>', 0)->select('users.id', 'users.name')]);

            $allTasks = $tasksQuery->get();
            $filteredTasks = $this->filterTasksByDate($allTasks, $startDate, $endDate);
            $reportData = $this->groupTasksByStaff($filteredTasks, $staffMembers);
        }

        if ($request->ajax()) {
            return view('Organization.reports._staff_report_table', [
                'reportData' => $reportData,
            ])->render();
        }

        return view('Organization.reports.staff', [
            'reportData' => $reportData,
            'active_period' => $period,
            'search' => $search,
        ]);
    }

    private function getDateRangeFromPeriod(string $period, ?string $customStart, ?string $customEnd): array
    {
        if ($period === 'custom' && $customStart && $customEnd) {
            return [Carbon::parse($customStart)->startOfDay(), Carbon::parse($customEnd)->endOfDay()];
        }
        $now = Carbon::now();
        switch ($period) {
            case 'day': return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'week': return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'month': return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'year': return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            case 'all': default: return [null, null];
        }
    }

    private function filterTasksByDate(Collection $tasks, ?Carbon $startDate, ?Carbon $endDate): Collection
    {
        if (!$startDate) return $tasks; // "All Time" period

        return $tasks->filter(function ($task) use ($startDate, $endDate) {
            if (!$task->is_recurring) {
                return $task->updated_at->between($startDate, $endDate);
            }
            $completedDates = (array) ($task->completed_at_dates ?? []);
            foreach ($completedDates as $dateString) {
                if (Carbon::parse($dateString)->between($startDate, $endDate)) {
                    return true;
                }
            }
            return false;
        });
    }

    private function groupTasksByClient(Collection $tasks): Collection
    {
        return $tasks->groupBy('client.name')->map(function ($clientTasks) {
            return $clientTasks->groupBy('service.name')->map(function ($serviceTasks) {
                return $serviceTasks->groupBy('job.name');
            });
        });
    }

    private function groupTasksByStaff(Collection $tasks, Collection $staffMembers): Collection
    {
        $reportData = collect();
        foreach ($staffMembers as $staff) {
            $staffServices = []; $staffTotalDuration = 0;
            foreach ($tasks as $task) {
                $staffOnTask = $task->staff->find($staff->id);
                if ($staffOnTask) {
                    $serviceId = $task->service->id ?? 'uncategorized';
                    $jobId = $task->job->id ?? 'uncategorized';
                    $staffDuration = $staffOnTask->pivot->duration_in_seconds;

                    if (!isset($staffServices[$serviceId])) {
                        $staffServices[$serviceId] = ['name' => $task->service->name ?? 'Uncategorized', 'jobs' => [], 'total_duration' => 0];
                    }
                    if (!isset($staffServices[$serviceId]['jobs'][$jobId])) {
                        $staffServices[$serviceId]['jobs'][$jobId] = ['name' => $task->job->name ?? 'Uncategorized', 'tasks' => [], 'total_duration' => 0];
                    }
                    $staffServices[$serviceId]['jobs'][$jobId]['tasks'][] = ['name' => $task->name, 'duration' => $staffDuration];
                    $staffServices[$serviceId]['jobs'][$jobId]['total_duration'] += $staffDuration;
                    $staffServices[$serviceId]['total_duration'] += $staffDuration;
                    $staffTotalDuration += $staffDuration;
                }
            }
            if (!empty($staffServices)) {
                $reportData->push((object)[
                    'staff_name' => $staff->name,
                    'services' => $staffServices,
                    'total_duration' => $staffTotalDuration,
                ]);
            }
        }
        return $reportData;
    }
}