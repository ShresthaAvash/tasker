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
use App\Models\ClientDocument; // Import the ClientDocument model
use Illuminate\Support\Collection; // Import the Collection class

class DashboardController extends Controller
{
    /**
     * Display the main dashboard for Organization Owners.
     */
    public function index()
    {
        $organizationId = Auth::id();
        $organization = Auth::user();

        // Stats for the info boxes
        $clientCount = User::where('organization_id', $organizationId)
            ->where('type', 'C')
            ->count();

        $staffCount = User::where('organization_id', $organizationId)
            ->whereIn('type', ['A', 'T'])
            ->count();

        $serviceCount = Service::where('organization_id', $organizationId)->count();

        $subscriptionCount = $organization->subscriptions()->where('stripe_status', 'active')->count();
        
        // --- THIS IS THE DEFINITIVE FIX FOR ALL DASHBOARD DATA ---

        // Count active tasks from assignments, not templates
        $activeTaskCount = AssignedTask::whereHas('client', fn($q) => $q->where('organization_id', $organizationId))
            ->whereIn('status', ['to_do', 'ongoing'])
            ->count();

        // Get upcoming tasks from assignments, not templates
        $upcomingTasks = AssignedTask::whereHas('client', fn($q) => $q->where('organization_id', $organizationId))
            ->whereIn('status', ['to_do', 'ongoing'])
            ->whereNotNull('start')
            ->where('start', '>=', now())
            ->whereHas('staff') // Ensure at least one staff member is assigned
            ->orderBy('start', 'asc')
            ->with(['client', 'service', 'job', 'staff'])
            ->limit(5)
            ->get();
            
        // Data for the pie chart from assignments, not templates
        $dbStatusCounts = AssignedTask::whereHas('client', fn($q) => $q->where('organization_id', $organizationId))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
        
        // Define the desired order and default values for the chart to ensure 'Ongoing' is always present.
        $allStatuses = collect([
            'to_do' => 0,
            'ongoing' => 0,
            'completed' => 0,
        ]);

        // Merge the database results with the defaults. This ensures all keys exist, even with a count of 0.
        $taskStatusCounts = $allStatuses->merge($dbStatusCounts);
        
        // --- END OF FIX ---

        $chartLabels = $taskStatusCounts->keys()->map(fn($s) => ucwords(str_replace('_', ' ', $s)));
        $chartData = $taskStatusCounts->values();

        return view('Organization.dashboard', compact(
            'clientCount',
            'staffCount',
            'serviceCount',
            'activeTaskCount',
            'subscriptionCount',
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

        // Get task counts by status from templates
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
        
        $toDoPersonalCount = Task::where('staff_id', $staffId)->whereNull('job_id')->where('status', 'to_do')->count();
        $toDoAssignedCount = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))->where('status', 'to_do')->count();
        $toDoTaskCount = $toDoPersonalCount + $toDoAssignedCount;

        $ongoingPersonalCount = Task::where('staff_id', $staffId)->whereNull('job_id')->where('status', 'ongoing')->count();
        $ongoingAssignedCount = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))->where('status', 'ongoing')->count();
        $ongoingTaskCount = $ongoingPersonalCount + $ongoingAssignedCount;

        $completedPersonalCount = Task::where('staff_id', $staffId)->whereNull('job_id')->where('status', 'completed')->count();
        $completedAssignedCount = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))->where('status', 'completed')->count();
        $completedTaskCount = $completedPersonalCount + $completedAssignedCount;
        
        $activeTaskCount = $toDoTaskCount + $ongoingTaskCount;

        $associatedClientIds = AssignedTask::whereHas('staff', function ($q) use ($staffId) {
                $q->where('users.id', $staffId);
            })
            ->distinct()
            ->pluck('client_id');

        $documentsCount = ClientDocument::whereIn('client_id', $associatedClientIds)->count();

        // --- THIS IS THE FIX ---
        // Data for Pie Chart now includes all statuses to prevent the "no data" issue.
        $chartData = collect([
            'to_do' => $toDoTaskCount,
            'ongoing' => $ongoingTaskCount,
            'completed' => $completedTaskCount,
        ]);
        // --- END OF FIX ---

        $chartLabels = $chartData->keys()->map(fn($s) => ucwords(str_replace('_', ' ', $s)));
        $chartDataValues = $chartData->values();

        $personalTasks = Task::where('staff_id', $staffId)
            ->whereNull('job_id')
            ->whereIn('status', ['to_do', 'ongoing'])
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

        $assignedTasks = AssignedTask::whereHas('staff', fn($q) => $q->where('users.id', $staffId))
            ->whereIn('status', ['to_do', 'ongoing'])
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
        
        $allTasks = $personalTasks->concat($assignedTasks);
        $upcomingTasks = $allTasks->sortBy('start')->take(10);
        
        return view('Organization.staff.dashboard', compact('activeTaskCount', 'completedTaskCount', 'upcomingTasks', 'chartLabels', 'chartDataValues', 'documentsCount'));
    }
}