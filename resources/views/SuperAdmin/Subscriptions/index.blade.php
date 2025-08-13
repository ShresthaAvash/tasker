@extends('layouts.app')
@section('page-content')
    <h3>All Subscriptions</h3>
    <a href="{{ route('superadmin.subscriptions.create') }}" class="btn btn-success mb-3">+ Add Subscription</a>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered table-striped">
        <thead>
            <tr><th>Name</th><th>Type</th><th>Price</th><th>Actions</th></tr>
        </thead>
        <tbody>
            @forelse ($subscriptions as $subscription)
                <tr>
                    <td>{{ $subscription->name }}</td>
                    <td>{{ ucfirst($subscription->type) }}</td>
                    <td>${{ number_format($subscription->price, 2) }}</td>
                    <td>
                        <a href="{{ route('superadmin.subscriptions.edit', $subscription->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form method="POST" action="{{ route('superadmin.subscriptions.destroy', $subscription->id) }}" class="d-inline" onsubmit="return confirm('Delete this subscription?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No subscriptions found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $subscriptions->links() }}</div>
@endsection