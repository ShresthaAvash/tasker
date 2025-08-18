<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Service;
use App\Models\Task;
use App\Models\Subscription;

class ReportController extends Controller
{
    /**
     * Generate and display a report based on the authenticated user's type.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $viewData = [];
        $viewName = '';

        // --- Super Admin Report ---
        if ($user->type === 'S') {
            $viewName = 'SuperAdmin.report';
            $viewData = [
                'organizationCount' => User::where('type', 'O')->count(),
                'subscriptionPlansCount' => Subscription::count(),
                'subscribedOrgsCount' => User::where('type', 'O')->whereNotNull('subscription_id')->count(),
                'totalMonthlyEarnings' => $this->calculateMonthlyEarnings(),
                'organizations' => User::where('type', 'O')->orderBy('name')->get(),
                'subscriptions' => Subscription::orderBy('name')->get(),
            ];
        }

        // --- Organization Report ---
        elseif (in_array($user->type, ['O', 'A'])) {
            $viewName = 'Organization.report';
            $organizationId = $user->organization_id ?? $user->id;
            
            $viewData = [
                'clientCount' => User::where('organization_id', $organizationId)->where('type', 'C')->count(),
                'staffCount' => User::where('organization_id', $organizationId)->whereIn('type', ['A', 'T'])->count(),
                'serviceCount' => Service::where('organization_id', $organizationId)->count(),
                'taskStatusCounts' => Task::whereHas('job.service', fn($q) => $q->where('organization_id', $organizationId))
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'clients' => User::where('organization_id', $organizationId)->where('type', 'C')->orderBy('name')->get(),
                'staff' => User::where('organization_id', $organizationId)->whereIn('type', ['A', 'T', 'O'])->orderBy('name')->get(),
                'services' => Service::where('organization_id', $organizationId)->withCount('jobs')->orderBy('name')->get(),
            ];
        }
        
        // --- Staff Report ---
        elseif ($user->type === 'T') {
            // --- THIS IS THE FIX ---
            // The view name must match the actual file path.
            $viewName = 'Organization.staff.report';
            // --- END OF FIX ---
            
            $staffId = $user->id;

            $viewData = [
                'personalTaskCount' => Task::where('staff_id', $staffId)->whereNull('job_id')->count(),
                'assignedTaskCount' => DB::table('assigned_task_staff')->where('user_id', $staffId)->count(),
                'activeTasks' => Task::where('staff_id', $staffId)->where('status', 'active')->get(),
            ];
        }

        if (empty($viewName)) {
            abort(404, 'Report not available for this user type.');
        }
        
        return view($viewName, $viewData);
    }

    /**
     * Helper function to calculate earnings for Super Admin.
     */
    private function calculateMonthlyEarnings()
    {
        $monthly = User::where('users.type', 'O')
            ->whereHas('subscription', fn($q) => $q->where('type', 'monthly'))
            ->join('subscriptions', 'users.subscription_id', '=', 'subscriptions.id')
            ->sum('subscriptions.price');

        $annual = User::where('users.type', 'O')
            ->whereHas('subscription', fn($q) => $q->where('type', 'annually'))
            ->join('subscriptions', 'users.subscription_id', '=', 'subscriptions.id')
            ->sum('subscriptions.price');
            
        return $monthly + ($annual / 12);
    }
}