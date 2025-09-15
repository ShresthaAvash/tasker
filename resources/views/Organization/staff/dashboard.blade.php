@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Dashboard</h1>
        <a href="{{ route('staff.tasks.index') }}" class="btn btn-primary">
            <i class="fas fa-tasks mr-1"></i> View Full Task List
        </a>
    </div>
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
    .bg-success-light { background-color: #d1e7dd; }
    .text-success { color: #198754 !important; }
    .bg-warning-light { background-color: #fff3cd; }
    .text-warning { color: #ffc107 !important; }

    /* Upcoming Tasks List */
    .list-group-item {
        border-left: 0;
        border-right: 0;
        padding: 1.25rem;
    }
</style>
@stop

@section('content')

<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-primary-light">
                    <i class="fas fa-tasks text-primary"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">My Active Tasks</p>
                    <h3 class="stat-number">{{ $activeTaskCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('staff.calendar') }}">
                    View My Calendar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-success-light">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Completed Tasks</p>
                    <h3 class="stat-number">{{ $completedTaskCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('staff.tasks.index') }}">
                    View All Tasks <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-warning-light">
                    <i class="fas fa-file-alt text-warning"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">My Documents</p>
                    <h3 class="stat-number">{{ $documentsCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('staff.documents.index') }}">
                    View All Documents <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="card-title">Task Overview</h3>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 300px;">
                @if($chartDataValues->sum() > 0)
                    <canvas id="taskStatusChart"></canvas>
                @else
                    <p class="text-center text-muted p-3">No task data available right now.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="card-title">My Upcoming Tasks</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($upcomingTasks as $task)
                        <li class="list-group-item">
                            <strong>{{ $task->display_name ?? $task->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $task->task_details }}</small>
                            <span class="float-right text-muted">
                                {{ optional($task->start)->diffForHumans() }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center p-4">
                            You have no upcoming tasks.
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
            if ({{ $chartDataValues->sum() }} > 0) {
                var taskCtx = document.getElementById('taskStatusChart').getContext('2d');
                var taskStatusChart = new Chart(taskCtx, {
                    type: 'pie',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            data: @json($chartDataValues),
                            backgroundColor: [ '#0d6efd', '#ffc107', '#28a745' ],
                            borderColor: [ '#ffffff', '#ffffff', '#ffffff' ],
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
                                    font: { size: 14 }
                                }
                            }
                        },
                    }
                });
            }
        });
    </script>
@stop