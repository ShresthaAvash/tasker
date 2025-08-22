@extends('layouts.app')

@section('title', 'My Reports')

@section('content_header')
    <h1>My Reports</h1>
@stop

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Task Progress by Service</h3>
    </div>
    <div class="card-body">
        @forelse($reportData as $data)
            <div class="mb-4">
                <h4>{{ $data['service']->name }}</h4>
                <div class="progress" style="height: 25px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $data['progress'] }}%;" aria-valuenow="{{ $data['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                        <strong>{{ $data['progress'] }}%</strong>
                    </div>
                </div>
                <small class="text-muted">{{ $data['completed_tasks'] }} of {{ $data['total_tasks'] }} tasks completed.</small>

                <div class="mt-2">
                    @foreach($data['service']->jobs as $job)
                        <p class="mb-0"><strong>Job: {{ $job->name }}</strong></p>
                        <ul>
                            @foreach($job->assignedTasks as $task)
                                <li>
                                    {{ $task->name }} - 
                                    @if($task->status === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @elseif($task->status === 'ongoing')
                                        <span class="badge badge-primary">Ongoing</span>
                                    @else
                                        <span class="badge badge-warning">To Do</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
                <hr>
            </div>
        @empty
            <p class="text-center text-muted">No services or tasks have been assigned to you yet.</p>
        @endforelse
    </div>
</div>
@stop