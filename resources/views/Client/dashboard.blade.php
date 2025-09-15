@extends('layouts.app')

@section('title', 'Client Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

{{-- NEW: Custom CSS for the modern dashboard UI --}}
@section('css')
<style>
    /* General Page & Card Styling */
    .content-wrapper {
        background-color: #f7f9fc;
    }
    .card {
        box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);
        border: none;
        border-radius: .75rem;
    }
    .card-header.bg-white {
        background-color: #fff !important;
        border-bottom: 1px solid #f0f0f0;
    }
    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #343a40;
    }

    /* Modern Stat Card Styling */
    .stat-card-modern {
        background-color: #fff;
        border-radius: .75rem;
        padding: 2.5rem;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    .stat-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .stat-card-content {
        display: flex;
        align-items: center;
        width: 100%;
    }
    .stat-card-modern .icon-wrapper {
        flex-shrink: 0;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 1.5rem;
    }
    .stat-card-modern .icon-wrapper i {
        font-size: 2rem;
    }
    .stat-card-modern .stat-info .stat-title {
        color: #6c757d;
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .stat-card-modern .stat-info .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
    }
    .stat-card-footer {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #f0f0f0;
        text-align: center;
    }
    .stat-card-footer a {
        color: #6c757d;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: color 0.2s ease-in-out;
    }
    .stat-card-footer a:hover {
        color: #0d6efd;
    }
    .stat-card-footer i {
        margin-left: 0.25rem;
        transition: transform 0.2s ease-in-out;
    }
    .stat-card-footer a:hover i {
        transform: translateX(3px);
    }

    /* Icon Colors */
    .bg-primary-light { background-color: #e3f2fd; }
    .text-primary { color: #0d6efd !important; }
    .bg-warning-light { background-color: #fff3cd; }
    .text-warning { color: #ffc107 !important; }
    .bg-info-light { background-color: #cff4fc; }
    .text-info { color: #0dcaf0 !important; }
    .bg-success-light { background-color: #d1e7dd; }
    .text-success { color: #198754 !important; }

    /* Recent Documents Table */
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-primary-light">
                    <i class="fas fa-tasks text-primary"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Total Tasks</p>
                    <h3 class="stat-number">{{ $stats['total'] }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('client.reports.index', ['month' => 'all']) }}">View All Tasks <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-warning-light">
                    <i class="fas fa-hourglass-start text-warning"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Tasks To Do</p>
                    <h3 class="stat-number">{{ $stats['to_do'] }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('client.reports.index', ['month' => 'all', 'statuses' => ['to_do']]) }}">View Tasks To Do <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-info-light">
                    <i class="fas fa-spinner text-info"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Ongoing Tasks</p>
                    <h3 class="stat-number">{{ $stats['ongoing'] }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('client.reports.index', ['month' => 'all', 'statuses' => ['ongoing']]) }}">View Ongoing Tasks <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-success-light">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Completed Tasks</p>
                    <h3 class="stat-number">{{ $stats['completed'] }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('client.reports.index', ['month' => 'all', 'statuses' => ['completed']]) }}">View Completed Tasks <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="card-title">Task Status Overview</h3>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 300px;">
                @if($chartDataValues->sum() > 0)
                    <canvas id="taskStatusChart"></canvas>
                @else
                    <p class="text-center text-muted">No task data available right now.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white">
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
                            <td colspan="4" class="text-center p-4 text-muted">No documents found.</td>
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
                                '#0d6efd', // Blue for To Do
                                '#ffc107', // Yellow for Ongoing
                                '#28a745'  // --- THIS IS THE FIX --- Green for Completed
                            ],
                            borderColor: '#ffffff',
                            borderWidth: 2
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