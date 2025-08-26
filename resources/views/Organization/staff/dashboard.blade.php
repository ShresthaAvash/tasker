@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Dashboard</h1>
        </a>
    </div>
@stop

@section('content')

<div class="row">
    {{-- Active Tasks Info Box --}}
    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $activeTaskCount }}</h3>
                <p>My Active Tasks</p>
            </div>
            <div class="icon">
                <i class="fas fa-tasks"></i>
            </div>
            <a href="{{ route('staff.calendar') }}" class="small-box-footer">
                View My Calendar <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $completedTaskCount }}</h3>
                <p>Completed Tasks</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('staff.tasks.index') }}" class="small-box-footer">
                View All Tasks <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    {{-- Upcoming Tasks List --}}
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
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
                        <li class="list-group-item text-muted text-center">
                            You have no upcoming tasks.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- NEW: Column for Pie Chart --}}
    <div class="col-md-4">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Task Overview</h3>
            </div>
            <div class="card-body">
                <canvas id="taskStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
    {{-- NEW: Chart.js script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function () {
            var ctx = document.getElementById('taskStatusChart').getContext('2d');
            var taskStatusChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        data: @json($chartData),
                        backgroundColor: [ 'rgba(255, 193, 7, 0.7)', 'rgba(40, 167, 69, 0.7)' ],
                        borderColor: [ 'rgba(255, 193, 7, 1)', 'rgba(40, 167, 69, 1)' ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'top' },
                }
            });
        });
    </script>
@stop