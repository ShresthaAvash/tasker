@extends('layouts.app')

@section('title', 'Activity Log')

@section('content_header')
    <h1>Activity Log</h1>
@stop

@section('content')
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title">My Recent Activity</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Date & Time (Local)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                <tr>
                    <td>{{ $activity->action }}</td>
                    <td>
                        {{-- --- THIS IS THE FIX --- --}}
                        {{-- It now converts the stored UTC time to your app's timezone --}}
                        {{ \Carbon\Carbon::parse($activity->created_at)->setTimezone(config('app.timezone'))->format('d M Y, h:i A') }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="2" class="text-center">No activity found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($activities->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $activities->links() }}
        </div>
    </div>
    @endif
</div>
@stop