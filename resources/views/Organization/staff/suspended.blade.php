@extends('layouts.app')

@section('title', 'Suspended Staff')

@section('content_header')
    <h1>Suspended Staff Members</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Suspended Staff</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $member)
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->designation->name ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-danger">Suspended</span>
                    </td>
                    <td>
                        <form action="{{ route('staff.toggleStatus', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to activate this staff member?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-xs btn-success">Activate</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">No suspended staff members found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $staff->links() }}
        </div>
    </div>
</div>
@stop