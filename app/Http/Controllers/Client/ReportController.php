<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AssignedTask;
use App\Models\Service;

class ReportController extends Controller
{
    public function index()
    {
        $client = Auth::user();

        // Get all services assigned to this client that have tasks
        $services = Service::whereHas('clients', function ($query) use ($client) {
            $query->where('users.id', $client->id);
        })->whereHas('jobs.assignedTasks', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })->with([
            'jobs.assignedTasks' => function ($query) use ($client) {
                $query->where('client_id', $client->id)
                      ->select('id', 'job_id', 'name', 'status');
            }
        ])->get();

        $reportData = $services->map(function ($service) {
            $totalTasks = 0;
            $completedTasks = 0;

            foreach ($service->jobs as $job) {
                $totalTasks += $job->assignedTasks->count();
                $completedTasks += $job->assignedTasks->where('status', 'completed')->count();
            }

            return [
                'service' => $service,
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'progress' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0,
            ];
        });

        return view('Client.report', compact('reportData'));
    }
}
