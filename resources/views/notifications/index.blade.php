@extends('layouts.app')

@section('title', 'All Notifications')

@section('content_header')
    <h1>Notifications</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($notifications as $notification)
                <li class="list-group-item {{ !$notification->read_at ? 'bg-light' : '' }}">
                    <div class="d-flex w-100 justify-content-between">
                        <p class="mb-1">
                            {{-- --- THIS IS THE FIX --- --}}
                            @if (isset($notification->data['organization_name']))
                                <i class="fas fa-user-plus text-success mr-2"></i>
                            @else
                                <i class="fas fa-tasks text-info mr-2"></i>
                            @endif
                            {{-- --- END OF FIX --- --}}
                            
                            {{ $notification->data['message'] }}
                        </p>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
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
@stop