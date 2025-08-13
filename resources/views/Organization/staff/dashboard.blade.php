@extends('layouts.app')

@section('page-content')

<div class="row">
    <div class="col-lg-4 col-12">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $activeTaskCount }}</h3>
                <p>Upcoming Tasks</p>
            </div>
            <div class="icon">
                <i class="fas fa-tasks"></i>
            </div>
            <a href="{{ route('staff.calendar') }}" class="small-box-footer">View Calendar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">My Upcoming Tasks</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($upcomingTasks as $task)
                        <li class="list-group-item">
                            <strong>{{ $task->display_name }}</strong>
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

@endsection