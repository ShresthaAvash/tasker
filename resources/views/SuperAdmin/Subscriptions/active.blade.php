@extends('layouts.app')

@section('title', 'Active Subscriptions')

@section('content_header')
    <h1>Active Subscriptions</h1>
@stop

@section('content')
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title">All Subscribed Organizations</h3>
    </div>
    <div class="card-body p-0">
         @if(session('success')) <div class="alert alert-success m-3">{{ session('success') }}</div> @endif
         @if(session('error')) <div class="alert alert-danger m-3">{{ session('error') }}</div> @endif
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Organization Name</th>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Starts On</th>
                    <th>Renews / Ends On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($organizations as $org)
                    @if($subscription = $org->subscription('default'))
                        <tr>
                            <td>{{ $org->name }}</td>
                            <td>{{ $org->email }}</td>
                            <td>
                                {{ $subscription->plan->name ?? 'Unknown Plan' }}
                                <span class="d-block text-muted small">
                                    £{{ number_format($subscription->plan->price ?? 0, 2) }} / {{ $subscription->plan->type ?? '' }}
                                </span>
                            </td>
                            <td>
                                @if($subscription->canceled())
                                    <span class="badge bg-warning">Canceled</span>
                                @else
                                    <span class="badge bg-success">{{ $subscription->stripe_status }}</span>
                                @endif
                            </td>
                            <td>
                                {{ $subscription->created_at->format('d M Y') }}
                            </td>
                            <td>
                                {{-- --- THIS IS THE FIX: Using the new calculated property --- --}}
                                @if ($subscription->calculated_ends_at)
                                    @if($subscription->canceled())
                                        <span class="text-danger">Ends on {{ $subscription->calculated_ends_at->format('d M Y') }}</span>
                                    @else
                                        Renews on {{ $subscription->calculated_ends_at->format('d M Y') }}
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($subscription->canceled())
                                    <form action="{{ route('superadmin.subscriptions.resume', $org) }}" method="POST" onsubmit="return confirm('Reactivate this subscription?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-xs btn-success">Reactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('superadmin.subscriptions.cancel', $org) }}" method="POST" onsubmit="return confirm('Cancel this subscription at the end of the current period?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-xs btn-danger">Deactivate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No active subscriptions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($organizations->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $organizations->links() }}
        </div>
    </div>
    @endif
</div>
@stop