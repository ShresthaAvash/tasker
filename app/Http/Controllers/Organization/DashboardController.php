<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Service;
use App\Models\Task;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard for Organization Owners.
     */
    public function index()
    {
        $organizationId = Auth::id();

        // Stats for the info boxes
        $clientCount = User::where('organization_id', $organizationId)->where('type', 'C')->count();
        $staffCount = User::where('organization_id', $organizationId)->whereIn('type', ['A', 'T'])->count();
        $serviceCount = Service::where('organization_id', $organizationId)->count();
        $activeTaskCount = Task::whereHas('job.service', fn($q) => $q->where('organization_id', $organizationId))
            ->where('status', 'active')->count();

        // --- THIS IS THE FIX ---
        // Get upcoming tasks and eager load the relationships we need to display.
        $upcomingTasks = Task::whereHas('job.service', fn($q) => $q->where('organization_id', $organizationId))
            ->where('status', 'active')
            ->whereNotNull('staff_id')
            ->whereNotNull('start')
            ->where('start', '>=', now())
            ->orderBy('start', 'asc')
            ->with(['staff', 'job', 'job.service']) // Eager load staff, job, and the job's service
            ->limit(5)
            ->get();
            
        // Data for the pie chart
        $taskStatusCounts = Task::whereHas('job.service', fn($q) => $q->where('organization_id', $organizationId))
            ->select('status', DB::raw('count(*) as count'))->groupBy('status')->pluck('count', 'status');
        $chartLabels = $taskStatusCounts->keys()->map(fn($s) => ucwords(str_replace('_', ' ', $s)));
        $chartData = $taskStatusCounts->values();

        return view('Organization.dashboard', compact(
            'clientCount', 'staffCount', 'serviceCount', 'activeTaskCount',
            'upcomingTasks', 'chartLabels', 'chartData'
        ));
    }

    /**
     * Display a simpler dashboard for Staff Members.
     */
    public function staffDashboard()
    {
        $staffId = Auth::id();
        $activeTaskCount = Task::where('staff_id', $staffId)->where('status', 'active')->count();
        $upcomingTasks = Task::where('staff_id', $staffId)
            ->where('status', 'active')->whereNotNull('start')->where('start', '>=', now())
            ->orderBy('start', 'asc')->limit(10)->get();

        return view('Organization.staff.dashboard', compact('activeTaskCount', 'upcomingTasks'));
    }
}