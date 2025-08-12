@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
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
            <a href="{{ route('organization.calendar') }}" class="small-box-footer">View My Calendar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    {{-- Upcoming Tasks List --}}
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">My Upcoming Tasks</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($upcomingTasks as $task)
                        <li class="list-group-item">
                            <strong>{{ $task->name }}</strong>
                            <span class="float-right text-muted">{{ $task->start->diffForHumans() }}</span>
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
</div>

@stop