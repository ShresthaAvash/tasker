@extends('layouts.app')

@section('title', 'Time Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Time Report</h1>
        <button onclick="window.print();" class="btn btn-primary d-print-none">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
@stop

@section('content')
<div class="card card-info card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
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
    <div class="card-body p-0">
        @if($tasks->isEmpty())
            <div class="text-center text-muted p-5">
                <h4>No Completed Tasks</h4>
                <p>There are no completed tasks with tracked time for the selected period.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Task</th>
                            <th>Client</th>
                            <th>Service / Job</th>
                            <th>Staff & Time Contributed</th>
                            <th class="text-right">Total Task Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td>{{ $task->name }}</td>
                                <td>{{ $task->client->name ?? 'N/A' }}</td>
                                <td>
                                    {{ $task->service->name ?? 'N/A' }} <br>
                                    <small class="text-muted">{{ $task->job->name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @if($task->staff->isEmpty())
                                        <em class="text-muted">No staff assigned</em>
                                    @else
                                        <ul class="list-unstyled mb-0">
                                            @foreach($task->staff as $staffMember)
                                                <li>
                                                    {{ $staffMember->name }}:
                                                    @if($task->staff->count() == 1)
                                                        <strong class="pl-2">{{ gmdate('H:i:s', $task->duration_in_seconds) }}</strong>
                                                    @else
                                                        <em class="text-muted pl-2" data-toggle="tooltip" title="Time is not tracked per-staff for tasks with multiple assignees.">Shared Time</em>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold">{{ gmdate('H:i:s', $task->duration_in_seconds) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@stop

@section('js')
<script>
    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })
</script>
@stop