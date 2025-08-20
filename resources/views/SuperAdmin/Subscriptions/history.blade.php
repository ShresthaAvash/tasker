@extends('layouts.app')

@section('title', 'Subscription History')

@section('content_header')
    <h1>Subscription History: {{ $organization->name }}</h1>
@stop

@section('content')
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title">All Past and Present Subscriptions</h3>
        <div class="card-tools">
            <a href="{{ route('superadmin.subscriptions.subscribed') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Subscribed List
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Plan Name</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $subscription)
                    <tr>
                        <td>{{ optional($subscription->plan)->name ?? 'Unknown Plan' }}</td>
                        <td>
                            @if($subscription->canceled())
                                <span class="badge badge-danger">Ended</span>
                            @else
                                <span class="badge badge-success">Active</span>
                            @endif
                        </td>
                        <td>{{ $subscription->created_at->format('d M Y') }}</td>
                        <td>{{ optional($subscription->ends_at)->format('d M Y') ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">No subscription history found for this organization.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop