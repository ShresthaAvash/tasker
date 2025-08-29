{{-- Full code for: tasker\resources\views\Client\dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Client Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        {{-- This is now the primary blue color --}}
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Tasks</p>
            </div>
            <div class="icon"><i class="fas fa-tasks"></i></div>
            <a href="{{ route('client.reports.index', ['month' => 'all']) }}" class="small-box-footer">View All Tasks <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['to_do'] }}</h3>
                <p>Tasks To Do</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-start"></i></div>
            <a href="{{ route('client.reports.index', ['month' => 'all', 'statuses' => ['to_do']]) }}" class="small-box-footer">View Tasks To Do <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3">
        {{-- Using the info color for variety within the blue theme --}}
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['ongoing'] }}</h3>
                <p>Ongoing Tasks</p>
            </div>
            <div class="icon"><i class="fas fa-spinner"></i></div>
            <a href="{{ route('client.reports.index', ['month' => 'all', 'statuses' => ['ongoing']]) }}" class="small-box-footer">View Ongoing Tasks <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['completed'] }}</h3>
                <p>Completed Tasks</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('client.reports.index', ['month' => 'all', 'statuses' => ['completed']]) }}" class="small-box-footer">View Completed Tasks <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- New Row for Pie Chart and Recent Documents --}}
<div class="row">
    {{-- Recent Documents - Now on the left --}}
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Recent Documents</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Uploaded By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDocuments as $document)
                        <tr>
                            <td>{{ $document->name }}</td>
                            <td><span class="badge {{ $document->uploader->type === 'C' ? 'badge-primary' : 'badge-info' }}">{{ $document->uploader->name }}</span></td>
                            <td>{{ $document->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('client.documents.download', $document) }}" class="btn btn-xs btn-secondary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No documents found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('client.documents.index') }}">View All Documents</a>
            </div>
        </div>
    </div>
    
    {{-- Task Status Overview (Pie Chart) - Now on the right --}}
    <div class="col-md-6"> {{-- Adjust column size as needed --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Task Status Overview</h3>
            </div>
            <div class="card-body">
                @if($chartDataValues->sum() > 0) {{-- Check if there's any data to display --}}
                    <canvas id="taskStatusChart"></canvas>
                @else
                    <p class="text-center text-muted p-3">No task data available right now.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function () {
            // Only render the chart if there is data
            if ({{ $chartDataValues->sum() }} > 0) {
                var ctx = document.getElementById('taskStatusChart').getContext('2d');
                var taskStatusChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            data: @json($chartDataValues),
                            backgroundColor: [
                                '#ffc107', // Warning (yellow) for To Do
                                '#17a2b8', // Info (teal) for Ongoing
                                '#28a745'  // Success (green) for Completed
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 14
                                    }
                                }
                            },
                        },
                    }
                });
            }
        });
    </script>
@stop