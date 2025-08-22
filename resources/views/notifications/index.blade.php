@extends('layouts.app')

@section('title', 'All Notifications')

@section('content_header')
    <h1>Notifications</h1>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($notifications as $notification)
                @php
                    $isMessageFromOrg = $notification->type === 'App\\Notifications\\MessageFromOrganization';
                    $isMessageSent = $notification->type === 'App\\Notifications\\MessageSentToClients';
                @endphp
                <li class="list-group-item {{ !$notification->read_at ? 'bg-light font-weight-bold' : '' }}">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div style="cursor: pointer; flex-grow: 1;"
                            class="notification-list-item"
                            data-id="{{ $notification->id }}"
                            data-read="{{ $notification->read_at ? 'true' : 'false' }}"
                            data-type="{{ class_basename($notification->type) }}"
                            data-info="{{ json_encode($notification->data) }}"
                            data-full-time="{{ $notification->created_at->format('d M Y, h:i A') }}"
                            data-recipients="{{ $isMessageSent ? $notification->data['recipients'] : '' }}"
                        >
                            <p class="mb-1">
                                @if ($isMessageFromOrg)
                                    <i class="fas fa-envelope text-primary mr-2"></i>
                                    <strong>{{ $notification->data['subject'] }}</strong> from {{ $notification->data['organization_name'] }}
                                @elseif($isMessageSent)
                                     <i class="fas fa-paper-plane text-success mr-2"></i>
                                     Message Sent: <strong>{{ $notification->data['subject'] }}</strong>
                                @elseif (isset($notification->data['organization_name']))
                                    <i class="fas fa-user-plus text-success mr-2"></i>
                                    {{ $notification->data['message'] }}
                                @else
                                    <i class="fas fa-tasks text-info mr-2"></i>
                                    {{ $notification->data['message'] }}
                                @endif
                            </p>
                            <small class="text-muted font-weight-normal">{{ $notification->created_at->diffForHumans() }}</small>
                        </div>
                        
                        @if($notification->read_at)
                            <form action="{{ route('notifications.markAsUnread', $notification->id) }}" method="POST" class="ml-3">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-outline-secondary">Mark as Unread</button>
                            </form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="list-group-item text-center text-muted">
                    You have no notifications.
                </li>
            @endforelse
        </ul>
    </div>
    @if($notifications->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $notifications->links() }}
        </div>
    </div>
    @endif
</div>
{{-- The modal itself is now loaded globally from layouts.app --}}
@stop

@section('js')
<script>
$(document).ready(function() {
    // The main notification modal is already added globally via app.blade.php
    let currentNotificationId = null;

    $('.notification-list-item').on('click', function() {
        const item = $(this);
        currentNotificationId = item.data('id');
        const type = item.data('type');
        const info = item.data('info');
        const fullTime = item.data('full-time');
        const recipients = item.data('recipients');

        if (type === 'MessageFromOrganization' || type === 'MessageSentToClients') {
            $('#notification-modal-subject').text(info.subject);
            $('#notification-modal-from').text(type === 'MessageSentToClients' ? 'System Confirmation' : info.organization_name);
            $('#notification-modal-message').text(type === 'MessageSentToClients' ? (info.full_message || info.message) : info.message);

            if (recipients) {
                $('#notification-modal-recipients').text(recipients);
                $('#notification-modal-recipients-wrapper').show();
            } else {
                $('#notification-modal-recipients-wrapper').hide();
            }

            $('#notification-modal-time').text(fullTime);
            $('#notificationDetailModal').modal('show');
        }
    });

    // The "Mark as Unread" button in the modal is handled by the global script in app.blade.php
});
</script>
@stop