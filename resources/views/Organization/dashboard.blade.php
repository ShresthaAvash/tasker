@extends('layouts.app')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')

{{-- Top Row Info Boxes --}}
<div class="row">
    {{-- Info Box Widgets --}}
    <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3>{{ $clientCount }}</h3><p>Active Clients</p></div><div class="icon"><i class="fas fa-users"></i></div><a href="{{ route('clients.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3>{{ $staffCount }}</h3><p>Staff Members</p></div><div class="icon"><i class="fas fa-user-tie"></i></div><a href="{{ route('staff.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3>{{ $activeTaskCount }}</h3><p>Active Tasks</p></div><div class="icon"><i class="fas fa-tasks"></i></div><a href="{{ route('organization.calendar') }}" class="small-box-footer">View Calendar <i class="fas fa-arrow-circle-right"></i></a></div></div>
    <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3>{{ $serviceCount }}</h3><p>Services Offered</p></div><div class="icon"><i class="fas fa-concierge-bell"></i></div><a href="{{ route('services.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a></div></div>
</div>

{{-- Main Content Row --}}
<div class="row">
    {{-- Left Column: Upcoming Team Tasks --}}
    <div class="col-md-7">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Upcoming Team Tasks</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($upcomingTasks as $task)
                        <li class="list-group-item">
                            {{-- --- THIS IS THE FIX --- --}}
                            {{-- Display Task, Job, and Service Name --}}
                            <strong>{{ $task->name }}</strong>
                            <br>
                            <small class="text-muted">
                                In Job: {{ $task->job->name ?? 'N/A' }} | 
                                Service: {{ $task->job->service->name ?? 'N/A' }} |
                                Assigned to: {{ $task->staff->name ?? 'N/A' }}
                            </small>
                            <span class="float-right text-muted">{{ $task->start->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">
                            No upcoming tasks assigned to the team.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Right Column: Quick Actions & Chart --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('clients.create') }}" class="btn btn-app bg-info"><i class="fas fa-users"></i> Add Client</a>
                <a href="{{ route('staff.create') }}" class="btn btn-app bg-success"><i class="fas fa-user-tie"></i> Add Staff</a>
                <a href="{{ route('services.create') }}" class="btn btn-app bg-danger"><i class="fas fa-concierge-bell"></i> Add Service</a>
            </div>
        </div>

        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Task Status Overview</h3>
            </div>
            <div class="card-body">
                <canvas id="taskStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

@stop

@section('js')
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
                        backgroundColor: [ 'rgba(108, 117, 125, 0.7)', 'rgba(0, 123, 255, 0.7)', 'rgba(220, 53, 69, 0.7)' ],
                        borderColor: [ 'rgba(108, 117, 125, 1)', 'rgba(0, 123, 255, 1)', 'rgba(220, 53, 69, 1)' ],
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