@extends('layouts.app')

@section('page-content')
    <h3>Subscription Activation Requests</h3>
    
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped">
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
            <div class="mt-3">
                {{ $requestedOrganizations->links() }}
            </div>
        </div>
    </div>
@endsection