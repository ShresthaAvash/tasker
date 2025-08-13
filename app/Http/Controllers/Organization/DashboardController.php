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
        // Get the next 5 upcoming tasks for the ENTIRE TEAM, not just the owner.
        $upcomingTasks = Task::whereHas('job.service', fn($q) => $q->where('organization_id', $organizationId))
            ->where('status', 'active')
            ->whereNotNull('staff_id') // Only show tasks that are actually assigned
            ->whereNotNull('start')
            ->where('start', '>=', now())
            ->orderBy('start', 'asc')
            ->with('staff') // Eager load the staff member's name for efficiency
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