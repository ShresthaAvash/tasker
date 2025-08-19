@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    @yield('page-content')
@stop

{{-- This section adds all custom items to the top-right of the navigation bar --}}
@section('content_top_nav_right')

    {{-- Notifications Dropdown Menu --}}
    <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            @if(Auth::check() && Auth::user()->unreadNotifications->count())
                <span class="badge badge-danger navbar-badge">{{ Auth::user()->unreadNotifications->count() }}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            @if(Auth::check())
                <span class="dropdown-item dropdown-header">{{ Auth::user()->unreadNotifications->count() }} New Notifications</span>
                <div class="dropdown-divider"></div>
                
                @forelse(Auth::user()->unreadNotifications->take(5) as $notification)
                    <a href="{{ route('notifications.index') }}" class="dropdown-item">
                        @if (isset($notification->data['organization_name']))
                            <i class="fas fa-user-plus text-success mr-2"></i>
                        @else
                            <i class="fas fa-tasks text-info mr-2"></i>
                        @endif
                        {{ Str::limit($notification->data['message'], 35) }}
                        <span class="float-right text-muted text-sm">{{ $notification->created_at->diffForHumans(null, true) }}</span>
                    </a>
                    <div class="dropdown-divider"></div>
                @empty
                    <span class="dropdown-item text-muted text-center">No new notifications</span>
                    <div class="dropdown-divider"></div>
                @endforelse

                <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">See All Notifications</a>
            @endif
        </div>
    </li>

    {{-- Active Timer (For Staff) --}}
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

@section('css')
    <style>
        /*
         * Increase the root font size to scale up the entire UI.
         * Default is 16px. 16px * 1.25 = 20px.
         * This makes the entire layout look like it's at 125% zoom by default.
        */
        html {
            font-size: 20px !important;
        }

        /* Force the main sidebar and the top brand link to have a pure white background */
        .main-sidebar, .brand-link {
            background-color: #ffffff !important;
        }
        
        /* Ensure the "Tasker" text is dark and readable on the white background */
        .brand-link .brand-text {
            color: #343a40 !important;
        }

        /* Add a subtle vertical line to the right of the sidebar for separation */
        .main-sidebar {
            border-right: 1px solid #dee2e6 !important;
        }
    </style>
@stop

@section('js')
    {{-- Any future global JavaScript can go here --}}
@stop