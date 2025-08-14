{{-- resources/views/layouts/app.blade.php --}}
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@endsection

@section('content')
    @yield('page-content')
@endsection

{{-- MODIFIED: Improved Global Timer Bar GUI --}}
@section('content_top_nav_right')
    @if(isset($activeTimer) && $activeTimer)
        <li class="nav-item">
            <div id="global-timer-bar" class="d-flex align-items-center bg-warning p-2 rounded"
                 data-task-id="{{ $activeTimer['task_id'] }}"
                 data-task-name="{{ $activeTimer['task_name'] }}"
                 data-initial-seconds="{{ $activeTimer['duration_in_seconds'] }}"
                 data-start-time="{{ $activeTimer['timer_started_at'] }}">
                <i class="fas fa-clock fa-spin mr-2"></i>
                <span id="global-timer-task-name" class="font-weight-bold mr-3">{{ $activeTimer['task_name'] }}</span>
                <span id="global-timer-display" class="mr-3">00:00:00</span>
                <button id="global-timer-stop-btn" class="btn btn-xs btn-danger">Stop</button>
            </div>
        </li>
    @endif
@endsection