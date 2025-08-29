@extends('layouts.app')

@section('title', 'View Organization')

@section('content_header')
    <h1>Organization: {{ $organization->name }}</h1>
@stop

@section('content')
    {{-- Organization Details Card --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">Organization Details</h3>
        </div>
        <div class="card-body">
            <p><strong>Name:</strong> {{ $organization->name }}</p>
            <p><strong>Email:</strong> {{ $organization->email }}</p>
            <p><strong>Phone:</strong> {{ $organization->phone ?? 'N/A' }}</p>
            <p><strong>Address:</strong> {{ $organization->address ?? 'N/A' }}</p>
            <p><strong>Status:</strong>
                @if ($organization->status === 'A')
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </p>
        </div>
    </div>

    {{-- Subscription History Card --}}
    <div class="card card-primary card-outline mt-4">
        <div class="card-header">
            <h3 class="card-title">Subscription History</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Plan Name</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Subscribed On</th>
                            <th>Renews / Ends On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($organization->subscriptions as $subscription)
                            <tr>
                                <td>{{ optional($subscription->plan)->name ?? 'N/A' }}</td>
                                <td>
                                    @if ($subscription->canceled())
                                        <span class="badge badge-warning">Canceled</span>
                                    @else
                                        <span class="badge badge-success">{{ ucfirst($subscription->stripe_status) }}</span>
                                    @endif
                                </td>
                                <td>${{ number_format(optional($subscription->plan)->price, 2) }} / {{ optional($subscription->plan)->type }}</td>
                                <td>{{ $subscription->created_at->format('d M Y') }}</td>
                                <td>
                                    @if ($date = $subscription->calculated_ends_at)
                                        @if($subscription->canceled())
                                            <span class="text-danger">Ends on {{ $date->format('d M Y') }}</span>
                                        @else
                                            Renews on {{ $date->format('d M Y') }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted p-4">This organization has no subscription history.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop