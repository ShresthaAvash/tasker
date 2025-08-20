{{-- resources/views/layouts/app.blade.php --}}
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    {{-- Allows child views to override the page title --}}
    <h1>@yield('page_title', 'Dashboard')</h1>
@endsection

@section('content')
    @yield('page-content')
@endsection

{{-- This section adds all custom items to the top-right of the navigation bar --}}
@section('content_top_nav_right')

    {{-- MODIFIED: Added a unique ID to the list item for precise CSS targeting --}}
    <li class="nav-item dropdown" id="notification-bell">
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
                    {{-- MODIFIED: The structure inside the link is improved for better layout --}}
                    <a href="{{ route('notifications.index') }}" class="dropdown-item">
                        <div class="notification-text">
                            @if (isset($notification->data['organization_name']))
                                <i class="fas fa-user-plus text-success mr-2"></i>
                            @else
                                <i class="fas fa-tasks text-info mr-2"></i>
                            @endif
                            {{ $notification->data['message'] }}
                        </div>
                        <span class="text-nowrap text-muted text-sm">{{ $notification->created_at->diffForHumans(null, true) }}</span>
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

    {{-- Legacy Active Timer Placeholder (kept for compatibility if needed) --}}
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

        /* --- SIDEBAR HIGHLIGHTING RULE REMOVED --- */

        /* Style for the active TAB on content pages */
        .card-tabs .nav-tabs .nav-link.active {
            background-color: #ffffff;
            border-top: 3px solid #17a2b8; /* Your teal color */
            border-left-color: #dee2e6;
            border-right-color: #dee2e6;
            border-bottom-color: transparent;
            color: #17a2b8;
        }
        
        /* --- THIS IS THE DEFINITIVE FIX --- */
        #notification-bell .dropdown-menu {
            min-width: 450px !important; /* Makes the box wider to fit the text */
            position: absolute !important;
            left: auto !important;
            right: 0 !important;
        }

        #notification-bell .dropdown-item {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            white-space: normal !important; /* Allows long text to wrap gracefully */
            padding-top: 10px;
            padding-bottom: 10px;
        }

        #notification-bell .notification-text {
            flex-grow: 1;
            padding-right: 15px; /* Adds space between the message and the timestamp */
        }
    </style>
@stop

{{-- Global JS including the new timer logic --}}
@section('js')
<script>
    $(document).ready(function() {
        // ============== GLOBAL TIMER SCRIPT START ==============

        let globalTimerInterval;

        function formatTime(totalSeconds) {
            if (isNaN(totalSeconds) || totalSeconds < 0) totalSeconds = 0;
            const hours = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
            const minutes = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
            const seconds = (totalSeconds % 60).toString().padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        }

        function renderGlobalTracker() {
            $('#global-live-tracker').remove();

            const timerData = JSON.parse(localStorage.getItem('runningTimer'));

            if (!timerData) {
                if (globalTimerInterval) clearInterval(globalTimerInterval);
                return;
            }

            const trackerHtml = `
                <div id="global-live-tracker"
                     class="alert alert-info d-flex justify-content-between align-items-center p-2 mb-4 shadow-sm"
                     style="position: sticky; top: 10px; z-index: 1050; display: none;"
                     role="alert">
                    <div>
                        <i class="fas fa-stopwatch fa-spin mr-2"></i>
                        <span class="font-weight-bold">Tracking:</span>
                        <span class="mx-2">${timerData.taskName}</span>
                        <span id="global-live-tracker-display" class="badge badge-dark" style="font-size: 1.1em; min-width: 80px;">00:00:00</span>
                    </div>
                    <button id="global-live-tracker-stop-btn" data-task-id="${timerData.taskId}" class="btn btn-danger btn-sm">
                        <i class="fas fa-stop"></i> Stop Timer
                    </button>
                </div>
            `;

            $('.content-wrapper .content').prepend(trackerHtml);
            $('#global-live-tracker').fadeIn();

            const duration = parseInt(timerData.duration, 10) || 0;
            const startTime = new Date(timerData.startedAt).getTime();
            const display = $('#global-live-tracker-display');

            if (globalTimerInterval) clearInterval(globalTimerInterval);

            const updateDisplay = () => {
                const now = new Date().getTime();
                const elapsed = Math.floor((now - startTime) / 1000);
                display.text(formatTime(duration + elapsed));
            };

            updateDisplay();
            globalTimerInterval = setInterval(updateDisplay, 1000);
        }

        $(document).on('click', '#global-live-tracker-stop-btn', function() {
            const button = $(this);
            const taskId = button.data('task-id');
            
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Stopping...');

            $.ajax({
                type: 'POST',
                url: `/staff/tasks/${taskId}/stop-timer`,
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    localStorage.removeItem('runningTimer');
                    if (globalTimerInterval) clearInterval(globalTimerInterval);
                    $('#global-live-tracker').fadeOut(400, () => $(this).remove());
                    
                    if (window.location.pathname.includes('/staff/tasks')) {
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    alert('Error: Could not stop the timer. Please refresh the page.');
                    button.prop('disabled', false).html('<i class="fas fa-stop"></i> Stop Timer');
                }
            });
        });

        renderGlobalTracker();
        window.renderGlobalTracker = renderGlobalTracker;

        // ============== GLOBAL TIMER SCRIPT END ==============
    });
</script>

{{-- Allow child views to push page-specific JS --}}
@yield('page_content_js')
@stop