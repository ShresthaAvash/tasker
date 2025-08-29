<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AssignedTask;
use App\Models\ClientDocument;
use Illuminate\Support\Collection; // Import Collection for chart data

class DashboardController extends Controller
{
    public function index()
    {
        $client = Auth::user();

        $taskStats = AssignedTask::where('client_id', $client->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $stats = [
            'total' => $taskStats->sum(),
            'completed' => $taskStats->get('completed', 0),
            'ongoing' => $taskStats->get('ongoing', 0),
            'to_do' => $taskStats->get('to_do', 0),
        ];
        
        $recentDocuments = ClientDocument::where('client_id', $client->id)
            ->with('uploader')
            ->latest()
            ->take(5)
            ->get();

        // NEW: Prepare data for the pie chart
        $chartData = collect([
            'to_do' => $stats['to_do'],
            'ongoing' => $stats['ongoing'],
            'completed' => $stats['completed'],
        ]);

        $chartLabels = $chartData->keys()->map(fn($s) => ucwords(str_replace('_', ' ', $s)));
        $chartDataValues = $chartData->values(); // Use a different variable name to avoid conflict with $chartData collection

        return view('Client.dashboard', compact('stats', 'recentDocuments', 'chartLabels', 'chartDataValues'));
    }
}