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

    <li class="nav-item dropdown" id="notification-bell">
        <a class="nav-link" data-toggle="dropdown" href="#">
            <i class="far fa-bell"></i>
            @if(Auth::check() && Auth::user()->unreadNotifications->count())
                <span class="badge badge-danger navbar-badge" id="notification-badge-count">{{ Auth::user()->unreadNotifications->count() }}</span>
            @endif
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
            @if(Auth::check())
                <span class="dropdown-item dropdown-header"><span id="notification-header-count">{{ Auth::user()->unreadNotifications->count() }}</span> New Notifications</span>
                <div class="dropdown-divider"></div>
                
                @forelse(Auth::user()->notifications()->take(5)->get() as $notification)
                    @php
                        $isMessageSent = $notification->type === 'App\\Notifications\\MessageSentToClients';
                    @endphp
                    <div class="dropdown-item notification-item {{ $notification->read_at ? '' : 'bg-light font-weight-bold' }}"
                         data-id="{{ $notification->id }}" 
                         data-type="{{ class_basename($notification->type) }}"
                         data-info="{{ json_encode($notification->data) }}"
                         data-full-time="{{ $notification->created_at->format('d M Y, h:i A') }}"
                         data-read="{{ $notification->read_at ? 'true' : 'false' }}"
                         data-recipients="{{ $isMessageSent ? $notification->data['recipients'] : '' }}"
                         style="cursor: pointer;">
                        
                        <div class="notification-text">
                            @if($notification->type === 'App\\Notifications\\MessageFromOrganization')
                                <i class="fas fa-envelope text-primary mr-2"></i>
                                <strong>{{ $notification->data['subject'] }}</strong> from {{ $notification->data['organization_name'] }}
                            @elseif($isMessageSent)
                                <i class="fas fa-paper-plane text-success mr-2"></i>
                                Message Sent: <strong>{{ $notification->data['subject'] }}</strong>
                            @else
                                <i class="fas fa-tasks text-info mr-2"></i>
                                {{ $notification->data['message'] }}
                            @endif
                        </div>
                        <span class="text-nowrap text-muted text-sm">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="dropdown-divider"></div>
                @empty
                    <span class="dropdown-item text-muted text-center">No notifications</span>
                    <div class="dropdown-divider"></div>
                @endforelse

                <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer">See All Notifications</a>
            @endif
        </div>
    </li>
@endsection

@section('css')
    <style>
        /* Base styles for a cleaner look */
        .main-sidebar, .brand-link { background-color: #ffffff !important; }
        .brand-link .brand-text { color: #343a40 !important; }
        .main-sidebar { border-right: 1px solid #dee2e6 !important; }

        /* --- THEME COLOR FIXES --- */
        /* This rule ensures that active tabs in cards use your primary blue color */
        .card-primary.card-tabs .nav-tabs .nav-link.active,
        .card-tabs .nav-tabs .nav-link.active {
            background-color: #0c6ffd !important;
            border-color: #0c6ffd #0c6ffd #ffffff !important;
            color: #ffffff !important;
        }
        .card-tabs .nav-tabs .nav-link { color: #007bff; }
        
        /* Notification dropdown styles */
        @import url('https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap');
        body {
            font-family: 'Figtree', sans-serif !important;
        }

        #notification-bell .dropdown-menu { min-width: 450px !important; position: absolute !important; left: auto !important; right: 0 !important; }
        .notification-item { display: flex !important; justify-content: space-between !important; align-items: center !important; white-space: normal !important; padding-top: 10px; padding-bottom: 10px; }
        .notification-text { flex-grow: 1; padding-right: 15px; }

        /* Modal styles for notifications */
        #notificationDetailModal .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        #notificationDetailModal .modal-title {
            font-weight: 600;
        }
        #notificationDetailModal .modal-body {
            background-color: #fff;
        }
        #notification-modal-message {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            white-space: pre-wrap;
            max-height: 40vh;
            overflow-y: auto;
        }

        /* --- Custom Sidebar Styles for Blue Theme and Font Consistency --- */
        /* For active main menu items and active parent menu items (like 'Organizations' when expanded) */
        .main-sidebar .nav-sidebar .nav-item > .nav-link.active,
        .main-sidebar .nav-sidebar .nav-item.menu-open > .nav-link { /* menu-open is for expanded parent */
            background-color: #0c6ffd !important; /* Primary blue background */
            color: #ffffff !important;           /* White text */
        }

        .main-sidebar .nav-sidebar .nav-item > .nav-link.active p,
        .main-sidebar .nav-sidebar .nav-item.menu-open > .nav-link p,
        .main-sidebar .nav-sidebar .nav-item > .nav-link.active i,
        .main-sidebar .nav-sidebar .nav-item.menu-open > .nav-link i {
            color: #ffffff !important;           /* White text and icons */
        }

        /* For active child menu items within a submenu */
        .main-sidebar .nav-sidebar .nav-treeview .nav-item > .nav-link.active {
            background-color: #0c6ffd !important; /* Primary blue background */
            color: #ffffff !important;           /* White text */
        }

        .main-sidebar .nav-sidebar .nav-treeview .nav-item > .nav-link.active p,
        .main-sidebar .nav-sidebar .nav-treeview .nav-item > .nav-link.active i {
            color: #ffffff !important;           /* White text and icons */
        }

        /* Ensure consistent font size for all sidebar items */
        .main-sidebar .nav-sidebar .nav-link,
        .main-sidebar .nav-sidebar .nav-link p,
        .main-sidebar .nav-sidebar .nav-link i.nav-icon { /* Target specific icon for size */
            font-size: 0.95rem; /* Consistent font size */
        }
        /* Ensure caret icons don't get too small if we target all <i> */
        .main-sidebar .nav-sidebar .nav-link i.right {
            font-size: 0.8rem !important; /* Smaller caret icon */
        }

        .main-sidebar .nav-header {
            font-size: 0.85rem; /* Headers can be slightly smaller */
            padding: 0.8rem 1rem;
            color: #6c757d; /* Default header text color */
        }

        /* --- Dashboard Quick Actions Specific Styling --- */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Responsive grid */
            gap: 15px; /* Space between buttons */
            padding: 10px; /* Add some padding around the buttons */
        }

        .action-button {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 15px 10px;
            font-size: 0.95rem;
            height: 90px; /* Fixed height for consistency */
            text-align: center;
            border-radius: 8px;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            color: #fff !important; /* Ensure text is white for themed buttons */
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }

        .action-button i {
            font-size: 1.8rem; /* Larger icon size */
            margin-bottom: 5px;
        }

        /* Specific fix for list-group-item borders in Recently Joined Organizations */
        .card-body .list-group-item {
            border-bottom: 1px solid rgba(0, 0, 0, .125) !important; /* Ensure border is visible */
        }
        .card-body .list-group-item:last-child {
            border-bottom: none !important; /* No border on the last item */
        }
    </style>
@stop

{{-- Global JS including the new timer logic --}}
@section('js')
<script>
    $(document).ready(function() {
        if ($('body').hasClass('sidebar-collapse')) {
            $('body').removeClass('sidebar-collapse');
        }

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
            if (!timerData) { if (globalTimerInterval) clearInterval(globalTimerInterval); return; }
            const trackerHtml = `<div id="global-live-tracker" class="alert alert-info d-flex justify-content-between align-items-center p-2 mb-4 shadow-sm" style="position: sticky; top: 10px; z-index: 1050; display: none;" role="alert"><div><i class="fas fa-stopwatch fa-spin mr-2"></i><span class="font-weight-bold">Tracking:</span><span class="mx-2">${timerData.taskName}</span><span id="global-live-tracker-display" class="badge badge-dark" style="font-size: 1.1em; min-width: 80px;">00:00:00</span></div><button id="global-live-tracker-stop-btn" data-task-id="${timerData.taskId}" class="btn btn-danger btn-sm"><i class="fas fa-stop"></i> Stop Timer</button></div>`;
            $('.content-wrapper .content').prepend(trackerHtml);
            $('#global-live-tracker').fadeIn();
            const duration = parseInt(timerData.duration, 10) || 0;
            const startTime = new Date(timerStartedAt).getTime();
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
                    if (window.location.pathname.includes('/staff/tasks')) { window.location.reload(); }
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
        
        // ============== NEW NOTIFICATION SCRIPT START ==============
        const notificationModalHTML = `
            <div class="modal fade" id="notificationDetailModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="notification-modal-subject"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p><strong>From:</strong> <span id="notification-modal-from"></span></p>
                            <p><strong>Date:</strong> <span id="notification-modal-time"></span></p>
                            <div id="notification-modal-recipients-wrapper" style="display:none;">
                                <p><strong>Sent To:</strong> <span id="notification-modal-recipients"></span></p>
                            </div>
                            <hr>
                            <p id="notification-modal-message"></p>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" id="mark-unread-btn">Mark as Unread</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>`;
        $('body').append(notificationModalHTML);

        let currentNotificationId = null;

        $('#notification-bell').on('click', '.notification-item', function() {
            const item = $(this);
            currentNotificationId = item.data('id');
            const type = item.data('type');
            const info = item.data('info');
            const fullTime = item.data('full-time');
            const recipients = item.data('recipients');
            let isRead = item.data('read') === 'true';

            if (!isRead) {
                $.post(`/notifications/${currentNotificationId}/read`, { _token: '{{ csrf_token() }}' })
                    .done(function() {
                        item.removeClass('bg-light font-weight-bold').data('read', 'true');
                        let count = parseInt($('#notification-badge-count').text()) - 1;
                        $('#notification-badge-count').text(count > 0 ? count : '');
                        $('#notification-header-count').text(count > 0 ? count : 0);
                        if (count <= 0) $('#notification-badge-count').hide();
                    });
            }

            // Populate and show modal
            if (type === 'MessageFromOrganization') {
                $('#notification-modal-subject').text(info.subject);
                $('#notification-modal-from').text(info.organization_name);
                $('#notification-modal-message').text(info.message);
                $('#notification-modal-recipients-wrapper').hide();

            } else if (type === 'MessageSentToClients') {
                $('#notification-modal-subject').text(info.subject);
                $('#notification-modal-from').text('System Confirmation');
                $('#notification-modal-message').text(info.full_message);
                $('#notification-modal-recipients').text(recipients);
                $('#notification-modal-recipients-wrapper').show();

            } else {
                 $('#notification-modal-subject').text('System Notification');
                 $('#notification-modal-from').text('System');
                 $('#notification-modal-message').text(info.message);
                 $('#notification-modal-recipients-wrapper').hide();
            }
            $('#notification-modal-time').text(fullTime);
            
            $('#notificationDetailModal').modal('show');
        });

        $('#mark-unread-btn').on('click', function() {
            if (!currentNotificationId) return;
            $.post(`/notifications/${currentNotificationId}/unread`, { _token: '{{ csrf_token() }}' })
                .done(function(response) {
                    // Update UI
                    $(`.notification-item[data-id="${currentNotificationId}"]`).addClass('bg-light font-weight-bold').data('read', 'false');
                    $('#notification-badge-count').text(response.unreadCount).show();
                    $('#notification-header-count').text(response.unreadCount);
                    $('#notificationDetailModal').modal('hide');
                });
        });
        // ============== NEW NOTIFICATION SCRIPT END ==============
    });
</script>

{{-- Allow child views to push page-specific JS --}}
@yield('page_content_js')
@stop