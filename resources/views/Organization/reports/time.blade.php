@extends('layouts.app')

@section('title', 'Time Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center d-print-none">
        <h1>Time Report</h1>
        <button onclick="window.print();" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
@stop

@section('css')
<style>
    .client-block .card-header {
        background-color: #6c757d;
        color: #fff;
        padding: 0;
    }
    .client-block .btn-link {
        color: #fff;
        text-decoration: none;
        font-size: 1.25rem;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .client-block .btn-link:hover {
        text-decoration: none;
    }
    .service-block {
        border: 1px solid #17a2b8;
        border-radius: .25rem;
        margin-bottom: 1.5rem;
    }
    .service-header {
        background-color: #17a2b8;
        color: #fff;
        padding: 1rem 1.25rem;
        font-size: 1.25rem;
        font-weight: 600;
    }
    .job-block {
        border-top: 1px solid #dee2e6;
    }
    .job-header {
        background-color: #f8f9fa;
        padding: .75rem 1.25rem;
        font-weight: bold;
    }
    .task-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .75rem 1.25rem;
        border-top: 1px solid #e9ecef;
    }
    .task-details {
        flex-grow: 1;
    }
    .staff-list .badge {
        font-size: 90%;
        margin-right: 5px;
    }
    .time-display {
        min-width: 100px;
        text-align: right;
        font-weight: bold;
        font-size: 1.1rem;
    }
    .collapse-icon {
        transition: transform 0.2s ease-in-out;
    }
    a[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    @media print {
        .client-header, .service-header {
            -webkit-print-color-adjust: exact; 
            color-adjust: exact;
        }
        .job-header {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }
    }
</style>
@stop

@section('content')
<div class="card card-info card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0 d-print-none">
        <ul class="nav nav-tabs" id="time-period-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $active_period == 'day' ? 'active' : '' }}" href="{{ route('organization.reports.time', ['period' => 'day']) }}">Today</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $active_period == 'week' ? 'active' : '' }}" href="{{ route('organization.reports.time', ['period' => 'week']) }}">This Week</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $active_period == 'month' ? 'active' : '' }}" href="{{ route('organization.reports.time', ['period' => 'month']) }}">This Month</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $active_period == 'year' ? 'active' : '' }}" href="{{ route('organization.reports.time', ['period' => 'year']) }}">This Year</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $active_period == 'all' ? 'active' : '' }}" href="{{ route('organization.reports.time', ['period' => 'all']) }}">All Time</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        @if($groupedTasks->isEmpty())
            <div class="text-center text-muted p-5">
                <h4>No Completed Tasks</h4>
                <p>There are no completed tasks with tracked time for the selected period.</p>
            </div>
        @else
            <div id="accordion-report">
                @foreach($groupedTasks as $clientName => $services)
                <div class="card client-block mb-3">
                    <div class="card-header" id="heading-{{ Str::slug($clientName) }}">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapse-{{ Str::slug($clientName) }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                <span><i class="fas fa-user-tie mr-2"></i> Client: {{ $clientName }}</span>
                                <span class="time-display client-total-time">00:00:00</span>
                            </button>
                        </h2>
                    </div>

                    <div id="collapse-{{ Str::slug($clientName) }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-parent="#accordion-report">
                        <div class="card-body client-body">
                            @foreach($services as $serviceName => $jobs)
                            <div class="service-block">
                                <div class="service-header d-flex justify-content-between align-items-center">
                                    <span>Service: {{ $serviceName }}</span>
                                    <span class="time-display service-total-time">00:00:00</span>
                                </div>
                                <div class="service-body">
                                    @foreach($jobs as $jobName => $tasks)
                                    <div class="job-block">
                                        <a href="#collapse-{{ Str::slug($clientName.$serviceName.$jobName) }}" class="job-header d-flex justify-content-between align-items-center" data-toggle="collapse" aria-expanded="true">
                                            <span><i class="fas fa-chevron-down collapse-icon mr-2"></i> Job: {{ $jobName }}</span>
                                            <span class="time-display job-total-time">00:00:00</span>
                                        </a>
                                        <div id="collapse-{{ Str::slug($clientName.$serviceName.$jobName) }}" class="collapse show">
                                            <div class="list-group list-group-flush">
                                                @foreach($tasks as $task)
                                                <div class="list-group-item task-list-item" data-task-time="{{ $task->duration_in_seconds }}">
                                                    <div class="task-details">
                                                        <strong>{{ $task->name }}</strong>
                                                        <div class="staff-list mt-2">
                                                            @if($task->staff->isNotEmpty())
                                                                <a href="#staff-time-{{$task->id}}" data-toggle="collapse" class="text-secondary small"><i class="fas fa-users mr-1"></i> Assigned Staff ({{ $task->staff->count() }}) <i class="fas fa-chevron-down collapse-icon ml-1"></i></a>
                                                                <div class="collapse mt-2 pl-3" id="staff-time-{{$task->id}}">
                                                                    @foreach($task->staff as $staffMember)
                                                                        <div>
                                                                            <span class="badge badge-light">{{ $staffMember->name }}:</span>
                                                                            <strong>{{ gmdate('H:i:s', $staffMember->pivot->duration_in_seconds) }}</strong>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <span class="text-muted small">No staff assigned</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="time-display task-total-time">
                                                        {{ gmdate('H:i:s', $task->duration_in_seconds) }}
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        function formatTime(totalSeconds) {
            const hours = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
            const minutes = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
            const seconds = (totalSeconds % 60).toString().padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        }

        function calculateTotals() {
            $('.client-block').each(function() {
                let clientTotalSeconds = 0;
                $(this).find('.service-block').each(function() {
                    let serviceTotalSeconds = 0;
                    $(this).find('.job-block').each(function() {
                        let jobTotalSeconds = 0;
                        $(this).find('.task-list-item').each(function() {
                            jobTotalSeconds += parseInt($(this).data('task-time')) || 0;
                        });
                        $(this).find('.job-total-time').text(formatTime(jobTotalSeconds));
                        serviceTotalSeconds += jobTotalSeconds;
                    });
                    $(this).find('.service-total-time').text(formatTime(serviceTotalSeconds));
                    clientTotalSeconds += serviceTotalSeconds;
                });
                $(this).find('.client-total-time').text(formatTime(clientTotalSeconds));
            });
        }
        
        calculateTotals();
    });
</script>
@stop