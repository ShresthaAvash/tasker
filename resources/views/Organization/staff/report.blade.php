@extends('layouts.app')

@section('title', 'My Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>My Report</h1>
        <button onclick="window.print();" class="btn btn-primary d-print-none">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-6 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $personalTaskCount }}</h3>
                <p>Personal Tasks</p>
            </div>
            <div class="icon"><i class="fas fa-user"></i></div>
        </div>
    </div>
    <div class="col-lg-6 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $assignedTaskCount }}</h3>
                <p>Assigned Client Tasks</p>
            </div>
            <div class="icon"><i class="fas fa-tasks"></i></div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="card-title">My Active Tasks</h3>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($activeTasks as $task)
                <li class="list-group-item">{{ $task->name }}</li>
            @empty
                <li class="list-group-item text-muted">You have no active tasks.</li>
            @endforelse
        </ul>
    </div>
</div>
@stop