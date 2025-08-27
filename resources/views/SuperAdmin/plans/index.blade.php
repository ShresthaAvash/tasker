@extends('layouts.app')

@section('title', 'Subscription Plans')

@section('content_header')
    <h1>All Subscription Plans</h1>
@stop

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Available Plans</h3>
        <div class="card-tools">
            <a href="{{ route('superadmin.plans.create') }}" class="btn btn-primary btn-sm">Add New Plan</a>
        </div>
    </div>
    <div class="card-body p-0">
        @if(session('success'))
            <div class="alert alert-success m-3">{{ session('success') }}</div>
        @endif
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($plans as $plan)
                    <tr>
                        <td>{{ $plan->name }}</td>
                        <td>{{ ucfirst($plan->type) }}</td>
                        <td>${{ number_format($plan->price, 2) }}</td>
                        <td>
                            <a href="{{ route('superadmin.plans.edit', $plan->id) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" action="{{ route('superadmin.plans.destroy', $plan->id) }}" class="d-inline" onsubmit="return confirm('Delete this plan?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No subscription plans found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($plans->hasPages())
        <div class="card-footer">
            {{ $plans->links() }}
        </div>
    @endif
</div>
@endsection