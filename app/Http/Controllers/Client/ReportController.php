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
            ->with(['service', 'job', 'staff' => function ($query) {
                $query->where('assigned_task_staff.duration_in_seconds', '>', 0);
            }]);

        if ($search) {
            $tasksQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('service', fn($sq) => $sq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('job', fn($jq) => $jq->where('name', 'like', "%{$search}%"));
            });
        }

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
        $groupedTasks = $this->groupTasksByService($tasks);

        if ($request->ajax()) {
            return view('Client._report_table', ['groupedTasks' => $groupedTasks])->render();
        }

        return view('Client.report', $this->getCommonViewData(
            ['groupedTasks' => $groupedTasks], $request, $startDate, $endDate
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

    private function groupTasksByService(Collection $tasks): Collection
    {
        return $tasks->groupBy('service.name')->map(fn($serviceTasks) =>
            $serviceTasks->groupBy('job.name')
        );
    }
}