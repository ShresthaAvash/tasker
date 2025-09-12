<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AssignedTask;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $client = Auth::user();
        $search = $request->input('search');
        [$startDate, $endDate] = $this->resolveDatesFromRequest($request);
        $statuses = $request->input('statuses', []);

        $tasksQuery = AssignedTask::query()
            ->where('client_id', $client->id)
            ->with(['service', 'staff']);

        if ($search) {
            $tasksQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            });
        }
        
        $tasks = $tasksQuery->get();
        $expandedTasks = $this->expandTasksForReport($tasks, $startDate, $endDate, $statuses);
        $groupedTasks = $this->groupTasksByService($expandedTasks);

        if ($request->ajax()) {
            return view('Client._report_table', ['groupedTasks' => $groupedTasks])->render();
        }

        return view('Client.report', $this->getCommonViewData(
            ['groupedTasks' => $groupedTasks], $request, $startDate, $endDate
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
                    $clonedStaff = new Collection();
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

    private function groupTasksByService(Collection $tasks): Collection
    {
        return $tasks->groupBy('service.name');
    }
}