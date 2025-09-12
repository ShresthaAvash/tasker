{{-- resources/views/layouts/app.blade.php --}}
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('meta_tags')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content_header')
    {{-- Allows child views to override the page title --}}
    <h1>@yield('page_title', 'Dashboard')</h1>
@endsection

@section('content')
    @yield('page-content')
@endsection

{{-- Custom menu items are injected here --}}
@section('usermenu_body')
    <div class="row">
        <div class="col-12 text-center">
            <a href="{{ route('profile.activity_log') }}"><i class="fas fa-list mr-2"></i> Activity Log</a>
        </div>
    </div>
@stop

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
                
                {{-- --- THIS IS THE FIX: The HTML structure of the notification item has been simplified --- --}}
                @forelse(Auth::user()->notifications()->take(5)->get() as $notification)
                    @php
                        $isMessageSent = $notification->type === 'App\\Notifications\\MessageSentToClients';
                    @endphp
                    <a href="#" class="dropdown-item notification-item {{ $notification->read_at ? '' : 'bg-light' }}"
                         data-id="{{ $notification->id }}" 
                         data-type="{{ class_basename($notification->type) }}"
                         data-info="{{ json_encode($notification->data) }}"
                         data-full-time="{{ $notification->created_at->format('d M Y, h:i A') }}"
                         data-read="{{ $notification->read_at ? 'true' : 'false' }}"
                         data-recipients="{{ $isMessageSent ? $notification->data['recipients'] : '' }}">
                        
                        <p class="notification-message {{ !$notification->read_at ? 'font-weight-bold' : '' }}">
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
                        </p>
                        <p class="notification-timestamp mb-0">
                            {{ $notification->created_at->diffForHumans() }}
                        </p>
                    </a>
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

@push('css')
    <style>
        /* --- THEME COLOR FIXES --- */
        .card-primary.card-tabs .nav-tabs .nav-link.active,
        .card-tabs .nav-tabs .nav-link.active {
            background-color: #0c6ffd !important;
            border-color: #0c6ffd #0c6ffd #ffffff !important;
            color: #ffffff !important;
        }
        .card-tabs .nav-tabs .nav-link { color: #007bff; }

        /* --- SIDEBAR FIXES --- */
        .brand-link .brand-image {
            max-height: 38px !important;
            width: auto !important;
            margin-left: 0.2rem !important;
            margin-top: -3px !important;
        }
        .brand-link {
            padding-top: 0.6rem !important;
            padding-bottom: 1rem !important;
        }

        /* --- THIS IS THE DEFINITIVE FIX FOR SIDEBAR HIGHLIGHTING --- */

        /* 1. Remove the gray background from any parent menu item that is open. */
        .sidebar-light-primary .nav-sidebar > .nav-item.menu-open > .nav-link {
            background-color: transparent !important;
        }

        /* 2. Remove the background and border from ANY active link. */
        .nav-sidebar .nav-link.active {
            background-color: transparent !important;
            box-shadow: none !important;
            border-left: 3px solid transparent !important;
        }

        /* 3. Style the active CHILD link's text and icon to be blue. */
        .nav-sidebar .nav-treeview .nav-link.active,
        .nav-sidebar .nav-treeview .nav-link.active > i {
            color: #0c6ffd !important;
        }
        .nav-sidebar .nav-treeview .nav-link.active > p {
             font-weight: 600;
        }

        /* 4. Style active TOP-LEVEL links (like Dashboard) to be blue. */
        .nav-sidebar > .nav-item:not(.has-treeview) > .nav-link.active,
        .nav-sidebar > .nav-item:not(.has-treeview) > .nav-link.active > i {
            color: #0c6ffd !important;
        }
        .nav-sidebar > .nav-item:not(.has-treeview) > .nav-link.active > p {
             font-weight: 600;
        }

        /* 5. Style the active PARENT link's text and icon to be blue. This is the fix. */
        .nav-sidebar > .nav-item.has-treeview.menu-open > .nav-link.active,
        .nav-sidebar > .nav-item.has-treeview.menu-open > .nav-link.active > i {
            color: #0c6ffd !important;
        }
        .nav-sidebar > .nav-item.has-treeview.menu-open > .nav-link.active > p {
             font-weight: 600;
        }

        .card-tabs .nav-tabs .nav-link { color: #007bff; }
    
    /* --- THIS IS THE FIX --- */
    #notification-bell .dropdown-menu {
        width: 380px !important;
        max-width: 380px !important;
    }
    .notification-item {
        white-space: normal !important;
        padding: 0.75rem 1rem !important;
    }
    .notification-item .notification-message {
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 0.25rem !important;
    }
    .notification-item .notification-timestamp {
        font-size: 0.75rem;
        color: #6c757d !important;
    }
    /* --- END OF FIX --- */

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

    .nav-sidebar .nav-treeview > .nav-item > .nav-link.active {
        background-color: #e9ecef !important;
        color: #212529 !important;
    }
    </style>
@endpush


@push('js')
<script>
    $(document).ready(function() {
        
        // --- THIS IS THE CRITICAL FIX for CSRF TOKENS ---
        // This script automatically adds the token to all AJAX requests.
        // It must be present in this layout file.
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // --- THIS IS THE DEFINITIVE FIX FOR THE SIDEBAR ---
        // This script ensures all sidebar submenus are expanded by default on every page load.
        setTimeout(function() {
            // Find all sidebar treeview items.
            $('.sidebar .nav-item.has-treeview').each(function () {
                // Force the 'menu-open' class on the parent li.
                $(this).addClass('menu-open');
                // Ensure the child ul (nav-treeview) is visible.
                $(this).children('.nav-treeview').css('display', 'block');
            });

            // Ensure the main sidebar is not in its collapsed 'mini' state on page load.
            if ($('body').hasClass('sidebar-collapse')) {
                $('body').removeClass('sidebar-collapse');
            }
        }, 300); // A small delay is crucial to ensure this runs AFTER AdminLTE's script.
        
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
        
        // ============== NOTIFICATION SCRIPT START ==============
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

        $('#notification-bell').on('click', '.notification-item', function(e) {
            e.preventDefault();
            const item = $(this);
            currentNotificationId = item.data('id');
            const type = item.data('type');
            const info = item.data('info');
            const fullTime = item.data('full-time');
            const recipients = item.data('recipients');
            let isRead = item.data('read') === 'true';

            if (!isRead) {
                $.post(`/notifications/${currentNotificationId}/read`)
                    .done(function() {
                        item.removeClass('bg-light font-weight-bold');
                        let count = parseInt($('#notification-badge-count').text()) - 1;
                        $('#notification-badge-count').text(count > 0 ? count : '');
                        $('#notification-header-count').text(count > 0 ? count : 0);
                        if (count <= 0) $('#notification-badge-count').hide();
                    });
            }

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
            $.post(`/notifications/${currentNotificationId}/unread`)
                .done(function(response) {
                    $(`.notification-item[data-id="${currentNotificationId}"]`).addClass('bg-light font-weight-bold').data('read', 'false');
                    $('#notification-badge-count').text(response.unreadCount).show();
                    $('#notification-header-count').text(response.unreadCount);
                    $('#notificationDetailModal').modal('hide');
                });
        });
        // ============== NOTIFICATION SCRIPT END ==============
    });
</script>
@endpush