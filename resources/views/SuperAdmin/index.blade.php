@extends('layouts.app')

@section('page-content')
    <h3>All Organizations</h3>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="{{ request('search') }}">
            <button class="btn btn-primary">Search</button>
        </div>
    </form>

    <a href="{{ route('superadmin.organizations.create') }}" class="btn btn-success mb-3">+ Add Organization</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Name</th><th>Email</th><th>Status</th><th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($organizations as $org)
                <tr>
                    <td>{{ $org->name }}</td>
                    <td>{{ $org->email }}</td>
                    <td>
                        @if ($org->status === 'A')
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Suspended</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('superadmin.organizations.show', $org->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('superadmin.organizations.edit', $org->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form method="POST" action="{{ route('superadmin.organizations.destroy', $org->id) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Toggle status?')">
                                {{ $org->status === 'A' ? 'Suspend' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No organizations found.</td></tr>
            @endforelse
        </tbody>
    </table>

@endsection
