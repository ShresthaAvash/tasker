<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Service;
use App\Models\Task;
use App\Models\AssignedTask;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard for Organization Owners.
     */
    public function index()
    {
        $organizationId = Auth::id();
        $organization = Auth::user(); // Get the authenticated user object

        // Stats for the info boxes
        $clientCount = User::where('organization_id', $organizationId)
            ->where('type', 'C')
            ->count();

        $staffCount = User::where('organization_id', $organizationId)
            ->whereIn('type', ['A', 'T'])
            ->count();

        $serviceCount = Service::where('organization_id', $organizationId)->count();

        // --- THIS IS THE MODIFIED LOGIC ---
        // Count the number of active subscriptions for the organization
        $subscriptionCount = $organization->subscriptions()->where('stripe_status', 'active')->count();
        // --- END OF MODIFICATION ---

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
            'subscriptionCount', // Pass the new count variable
            'upcomingTasks',
            'chartLabels',
            'chartData'
        ));
    }

    /**
     * Display a full report for the organization.
     */
    public function report()
    {
        $organizationId = Auth::id();

        // Get all summary stats
        $clientCount = User::where('organization_id', $organizationId)->where('type', 'C')->count();
        $staffCount = User::where('organization_id', $organizationId)->whereIn('type', ['A', 'T'])->count();
        $serviceCount = Service::where('organization_id', $organizationId)->count();

        // Get task counts by status
        $taskStatusCounts = Task::whereHas('job.service', fn($q) => $q->where('organization_id', $organizationId))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Get detailed lists
        $clients = User::where('organization_id', $organizationId)->where('type', 'C')->orderBy('name')->get();
        $staff = User::where('organization_id', $organizationId)->whereIn('type', ['A', 'T', 'O'])->orderBy('name')->get();
        $services = Service::where('organization_id', $organizationId)->withCount('jobs')->orderBy('name')->get();

        return view('Organization.report', compact(
            'clientCount',
            'staffCount',
            'serviceCount',
            'taskStatusCounts',
            'clients',
            'staff',
            'services'
        ));
    }

    /**
     * Display a simpler dashboard for Staff Members.
     */
    public function staffDashboard()
    {
        $staffId = Auth::id();

        // Calculate all counts first.
        
        // 1. Active Personal Tasks Count
        $activePersonalCount = Task::where('staff_id', $staffId)
            ->whereNull('job_id')
            ->where('status', 'active')
            ->count();

        // 2. Active Assigned Tasks Count
        $activeAssignedCount = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
            ->where('status', '!=', 'completed')
            ->count();
        
        $activeTaskCount = $activePersonalCount + $activeAssignedCount;

        // 3. Completed Tasks Count (Both Personal and Assigned)
        $completedPersonalCount = Task::where('staff_id', $staffId)->whereNull('job_id')->where('status', 'completed')->count();
        $completedAssignedCount = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))->where('status', 'completed')->count();
        $completedTaskCount = $completedPersonalCount + $completedAssignedCount;
        
        // Count ongoing tasks
        $ongoingPersonalCount = Task::where('staff_id', $staffId)->whereNull('job_id')->where('status', 'ongoing')->count();
        $ongoingAssignedCount = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))->where('status', 'ongoing')->count();
        $ongoingTaskCount = $ongoingPersonalCount + $ongoingAssignedCount;

        // Data for Pie Chart
        $chartLabels = ['Ongoing Tasks', 'Completed Tasks'];
        $chartData = [$ongoingTaskCount, $completedTaskCount];

        // Fetch Upcoming Personal tasks
        $personalTasks = Task::where('staff_id', $staffId)
            ->whereNull('job_id')
            ->where('status', 'active')
            ->whereNotNull('start')
            ->where('start', '>=', now())
            ->orderBy('start', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                $task->display_name = $task->name;
                $task->task_details = 'Personal Task';
                return $task;
            });

        // Fetch Upcoming Assigned client tasks
        $assignedTasks = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
            ->where('status', '!=', 'completed')
            ->whereNotNull('start')
            ->where('start', '>=', now())
            ->with(['client', 'service', 'job'])
            ->orderBy('start', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($task) {
                $task->display_name = $task->name;
                $task->task_details = "Service: {$task->service->name} | Job: {$task->job->name} | Client: {$task->client->name}";
                return $task;
            });

        // Combine and sort tasks for the upcoming list
        $allTasks = $personalTasks->concat($assignedTasks);
        $upcomingTasks = $allTasks->sortBy('start')->take(10);
        
        return view('Organization.staff.dashboard', compact('activeTaskCount', 'completedTaskCount', 'upcomingTasks', 'chartLabels', 'chartData'));
    }
}