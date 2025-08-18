@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    @yield('page-content')
@stop

{{-- This section adds custom items to the top-right of the navigation bar. --}}
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
            <div id="global-timer-bar" class="d-flex align-items-center bg-warning p-2 rounded">
                 {{-- ... timer details ... --}}
            </div>
        </li>
    @endif
@endsection

{{-- THE CSS SECTION IS NOW EMPTY --}}
@section('css')
@stop

{{-- THE JS SECTION IS NOW EMPTY --}}
@section('js')
@stop