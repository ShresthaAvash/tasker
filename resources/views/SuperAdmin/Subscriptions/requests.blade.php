@extends('layouts.app')

@section('title', 'Subscription Requests')

@section('content_header')
    <h1>Subscription Activation Requests</h1>
@stop

@section('content')
<div class="card card-warning card-outline">
    <div class="card-header">
        <h3 class="card-title">Pending Activations</h3>
    </div>
    <div class="card-body p-0">
        @if(session('success'))
            <div class="alert alert-success m-3">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger m-3">{{ session('error') }}</div>
        @endif

        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Organization Name</th>
                    <th>Email</th>
                    <th>Requested On</th>
                    <th>Status</th>
                    <th style="width: 150px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requestedOrganizations as $org)
                    <tr>
                        <td>{{ $org->name }}</td>
                        <td>{{ $org->email }}</td>
                        <td>{{ $org->created_at->format('d M Y, H:i') }}</td>
                        <td><span class="badge bg-warning">Requested</span></td>
                        <td>
                            <form method="POST" action="{{ route('superadmin.subscriptions.approve', $org->id) }}" onsubmit="return confirm('Are you sure you want to activate this organization?');">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm">Approve</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No pending requests found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $requestedOrganizations->links() }}
    </div>
</div>
@endsection