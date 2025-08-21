<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AssignedTask;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    /**
     * Display the time tracking report for the organization.
     */
    public function timeReport(Request $request)
    {
        $organizationId = Auth::id();
        $period = $request->input('period', 'month'); // Default to this month
        [$startDate, $endDate] = $this->getDateRangeFromPeriod($period);

        $tasksQuery = AssignedTask::where('status', 'completed')
            ->where('duration_in_seconds', '>', 0) // Only fetch tasks that have time tracked
            ->whereHas('client', fn($q) => $q->where('organization_id', $organizationId))
            // --- THIS IS THE FIX: The '.pivot' has been removed ---
            ->with(['client', 'service', 'job', 'staff']); 

        // Fetch tasks and then filter by date in PHP to handle recurring tasks correctly
        $allCompletedTasks = $tasksQuery->get();

        $filteredTasks = $allCompletedTasks->filter(function ($task) use ($startDate, $endDate) {
            if (!$startDate) return true; // "All Time" period

            if (!$task->is_recurring) {
                // For non-recurring, the completion date is when the model was last updated to 'completed'
                return $task->updated_at->between($startDate, $endDate);
            }

            // For recurring tasks, check if any completion date is in the range
            $completedDates = (array) ($task->completed_at_dates ?? []);
            foreach ($completedDates as $dateString) {
                if (Carbon::parse($dateString)->between($startDate, $endDate)) {
                    return true;
                }
            }
            return false;
        });
        
        // Group tasks by Client -> Service -> Job for the view
        $groupedTasks = $this->groupTasks($filteredTasks);

        return view('Organization.reports.time', [
            'groupedTasks' => $groupedTasks->sortKeys(), // Sort clients alphabetically
            'active_period' => $period,
        ]);
    }

    /**
     * Calculates start and end dates based on a string period.
     */
    private function getDateRangeFromPeriod(string $period): array
    {
        $now = Carbon::now();
        switch ($period) {
            case 'day':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay()];
            case 'week':
                return [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()];
            case 'month':
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
            case 'year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear()];
            case 'all':
            default:
                return [null, null]; // No date filtering for "all time"
        }
    }

    /**
     * Groups a collection of tasks by Client, then Service, then Job.
     */
    private function groupTasks(Collection $tasks): Collection
    {
        return $tasks->groupBy('client.name')->map(function ($clientTasks) {
            return $clientTasks->groupBy('service.name')->map(function ($serviceTasks) {
                return $serviceTasks->groupBy('job.name');
            });
        });
    }
}