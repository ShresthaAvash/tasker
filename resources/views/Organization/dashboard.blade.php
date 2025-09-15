@extends('layouts.app')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

{{-- NEW: Custom CSS for the final modern dashboard UI --}}
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
    
    /* Stat Card Footer with larger button style */
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
        font-size: 0.95rem; /* Increased font size */
        transition: color 0.2s ease-in-out;
        display: block;
        padding: 0.25rem 0; /* Added padding */
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
    .bg-success-light { background-color: #d1e7dd; }
    .text-success { color: #198754 !important; }
    .bg-danger-light { background-color: #f8d7da; }
    .text-danger { color: #dc3545 !important; }
    .bg-purple-light { background-color: #e9d5ff; }
    .text-purple { color: #9333ea !important; }

    /* Quick Actions Card with better button styling */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem; /* Increased gap */
        height: 100%;
    }
    .action-button {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 0.5rem;
        font-size: 1rem; /* Increased font size */
        font-weight: 600;
        border-radius: .75rem;
        transition: all 0.2s ease-in-out;
        border: 1px solid #e3e6f0;
        background-color: #fff;
        color: #343a40;
        text-align: center;
        text-decoration: none;
        height: auto; /* Allow button to grow */
    }
    .action-button:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        border-color: #0d6efd;
        color: #0d6efd;
    }
    .action-button i {
        font-size: 1.75rem;
        margin-right: 0.75rem; /* Added margin */
        color: #0d6efd;
    }
    .list-group-item {
        border-left: 0;
        border-right: 0;
    }
</style>
@stop

@section('content')

{{-- Top Row Info Boxes - Redesigned --}}
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-primary-light">
                    <i class="fas fa-users text-primary"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Active Clients</p>
                    <h3 class="stat-number">{{ $clientCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('clients.index') }}">
                    View All Clients <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-success-light">
                    <i class="fas fa-user-tie text-success"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Staff Members</p>
                    <h3 class="stat-number">{{ $staffCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('staff.index') }}">
                    View All Members <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-danger-light">
                    <i class="fas fa-concierge-bell text-danger"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Services Offered</p>
                    <h3 class="stat-number">{{ $serviceCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('services.index') }}">
                    View All Services <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-purple-light">
                    <i class="fas fa-tags text-purple"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">My Subscription</p>
                    <h3 class="stat-number">{{ $subscriptionCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('organization.subscription.index') }}">
                    View Subscription <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Main Content Row - Reordered --}}
<div class="row">
    <div class="col-lg-7 d-flex flex-column">
        {{-- Quick Actions --}}
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions-grid">
                    <a href="{{ route('clients.create') }}" class="action-button"><i class="fas fa-users"></i> Add Client</a>
                    <a href="{{ route('staff.create') }}" class="action-button"><i class="fas fa-user-tie"></i> Add Staff</a>
                    <a href="{{ route('services.create') }}" class="action-button"><i class="fas fa-concierge-bell"></i> Add Service</a>
                    <a href="{{ route('pricing') }}" class="action-button"><i class="fas fa-tags"></i> Change Plan</a>
                </div>
            </div>
        </div>
        
        {{-- Pie Charts --}}
        <div class="row flex-grow-1">
            <div class="col-md-6 d-flex flex-column">
                <div class="card flex-grow-1">
                    <div class="card-header bg-white">
                        <h3 class="card-title">Task Status Overview</h3>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        @if($chartData->isNotEmpty() && $chartData->sum() > 0)
                            <canvas id="taskStatusChart"></canvas>
                        @else
                            <p class="text-muted">No task data to display.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex flex-column">
                <div class="card flex-grow-1">
                    <div class="card-header bg-white">
                        <h3 class="card-title">Service Distribution</h3>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        @if($serviceChartData->isNotEmpty() && $serviceChartData->sum() > 0)
                            <canvas id="serviceDistributionChart"></canvas>
                        @else
                            <p class="text-muted">No service data to display.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5 d-flex flex-column">
        {{-- Upcoming Team Tasks --}}
        <div class="card flex-grow-1">
            <div class="card-header bg-white">
                <h3 class="card-title">Upcoming Team Tasks</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($upcomingTasks as $task)
                        <li class="list-group-item">
                            <strong>{{ $task->name }}</strong>
                            <br>
                            <small class="text-muted">
                                Service: {{ optional($task->service)->name ?? 'N/A' }} |
                                Client: {{ optional($task->client)->name ?? 'N/A' }} |
                                Assigned to: {{ $task->staff->pluck('name')->join(', ') }}
                            </small>
                            <span class="float-right text-muted">{{ $task->start->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center p-4">
                            No upcoming tasks assigned to the team.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function () {
            // Task Status Chart
            @if($chartData->isNotEmpty() && $chartData->sum() > 0)
                var taskCtx = document.getElementById('taskStatusChart').getContext('2d');
                var taskStatusChart = new Chart(taskCtx, {
                    type: 'pie',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            data: @json($chartData),
                            backgroundColor: [ '#0d6efd', '#ffc107', '#28a745' ],
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
            @endif

            // Service Distribution Chart
            @if($serviceChartData->isNotEmpty() && $serviceChartData->sum() > 0)
                var serviceCtx = document.getElementById('serviceDistributionChart').getContext('2d');
                var serviceDistributionChart = new Chart(serviceCtx, {
                    type: 'pie',
                    data: {
                        labels: @json($serviceChartLabels),
                        datasets: [{
                            data: @json($serviceChartData),
                            backgroundColor: [
                                '#6f42c1', '#fd7e14', '#20c997', '#6610f2',
                                '#17a2b8', '#d63384', '#ffc107', '#343a40'
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
                        }
                    }
                });
            @endif
        });
    </script>
@stop