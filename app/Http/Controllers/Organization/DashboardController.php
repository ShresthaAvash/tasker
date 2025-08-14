<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Service;
use App\Models\Task;
use App\Models\AssignedTask; // Import AssignedTask

class DashboardController extends Controller
{
    /**
     * Display the main dashboard for Organization Owners.
     */
    public function index()
    {
        $organizationId = Auth::id();

        // Stats for the info boxes
        $clientCount = User::where('organization_id', $organizationId)
            ->where('type', 'C')
            ->count();

        $staffCount = User::where('organization_id', $organizationId)
            ->whereIn('type', ['A', 'T'])
            ->count();

        $serviceCount = Service::where('organization_id', $organizationId)->count();

        $activeTaskCount = Task::whereHas('job.service', fn($q) => $q->where('organization_id', $organizationId))
            ->where('status', 'active')
            ->count();

        // Get upcoming tasks and eager load the relationships we need to display.
        $upcomingTasks = Task::whereHas('job.service', fn($q) => $q->where('organization_id', $organizationId))
            ->where('status', 'active')
            ->whereNotNull('staff_id')
            ->whereNotNull('start')
            ->where('start', '>=', now())
            ->orderBy('start', 'asc')
            ->with(['staff', 'job', 'job.service'])
            ->limit(5)
            ->get();
            
        // Data for the pie chart
        $taskStatusCounts = Task::whereHas('job.service', fn($q) => $q->where('organization_id', $organizationId))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $chartLabels = $taskStatusCounts->keys()->map(fn($s) => ucwords(str_replace('_', ' ', $s)));
        $chartData = $taskStatusCounts->values();

        return view('Organization.dashboard', compact(
            'clientCount',
            'staffCount',
            'serviceCount',
            'activeTaskCount',
            'upcomingTasks',
            'chartLabels',
            'chartData'
        ));
    }

    /**
     * Display a simpler dashboard for Staff Members.
     */
    public function staffDashboard()
    {
        $staffId = Auth::id();

        // Personal tasks
        $personalTasks = Task::where('staff_id', $staffId)
            ->where('status', 'active')
            ->whereNotNull('start')
            ->where('start', '>=', now())
            ->orderBy('start', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                $task->display_name = $task->name;
                return $task;
            });

        // Assigned client tasks
        $assignedTasks = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
            ->where('status', 'pending')
            ->whereNotNull('start')
            ->where('start', '>=', now())
            ->with('client')
            ->get()
            ->map(function ($task) {
                $task->display_name = $task->client->name . ': ' . $task->name;
                return $task;
            });

        // Combine and sort tasks
        $allTasks = $personalTasks->concat($assignedTasks);
        $upcomingTasks = $allTasks->sortBy('start')->take(10);
        $activeTaskCount = $allTasks->count();

        return view('Organization.staff.dashboard', compact('activeTaskCount', 'upcomingTasks'));
    }
}
