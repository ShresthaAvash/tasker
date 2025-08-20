@extends('layouts.app')

@section('title', 'My Subscription')

@section('content_header')
    <h1>My Subscription</h1>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{ session('success') }}
    </div>
@endif

<div class="card card-info card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" id="subscription-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="current-plan-tab" data-toggle="pill" href="#current-plan" role="tab">Current Plan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="history-tab" data-toggle="pill" href="#history" role="tab">History</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="subscription-tabs-content">
            {{-- Current Plan Tab --}}
            <div class="tab-pane fade show active" id="current-plan" role="tabpanel">
                @if($plan)
                    <h3 class="text-info">{{ $plan->name }}</h3>
                    <p class="lead"><b>£{{ number_format($plan->price, 2) }}</b> / {{ $plan->type }}</p>
                    <p class="text-muted">{{ $plan->description }}</p>
                    <hr>
                    <p><strong>Started On:</strong> {{ $currentSubscription->created_at->format('d M Y') }}</p>
                    <p>
                        <strong>Status:</strong>
                        @if($currentSubscription->canceled())
                            <span class="badge badge-warning">Canceled</span>
                            Your subscription will end on <strong>{{ $currentSubscription->calculated_ends_at->format('d M Y') }}</strong>.
                        @else
                            <span class="badge badge-success">Active</span>
                            Your subscription will automatically renew on <strong>{{ $currentSubscription->calculated_ends_at->format('d M Y') }}</strong>.
                        @endif
                    </p>
                    <a href="{{ route('pricing') }}" class="btn btn-primary mt-3">Change Plan</a>
                @else
                    <p class="text-muted">You are not currently subscribed to any plan.</p>
                    <a href="{{ route('pricing') }}" class="btn btn-primary">View Plans</a>
                @endif
            </div>

            {{-- History Tab --}}
            <div class="tab-pane fade" id="history" role="tabpanel">
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
                        @forelse($allSubscriptions as $subscription)
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
                                <td colspan="4" class="text-center text-muted">No subscription history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop