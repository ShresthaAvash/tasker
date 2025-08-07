@extends('layouts.app')

@section('title', 'Staff Designations')

@section('content_header')
    <h1>Staff Designations</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Designations</h3>
        <div class="card-tools">
            <a href="{{ route('staff-designations.create') }}" class="btn btn-primary btn-sm">Add New Designation</a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width: 10px">#</th>
                    <th>Name</th>
                    <th style="width: 150px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($designations as $designation)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $designation->name }}</td>
                    <td>
                        <a href="{{ route('staff-designations.edit', $designation->id) }}" class="btn btn-xs btn-warning">Edit</a>
                        
                        <form action="{{ route('staff-designations.destroy', $designation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this designation?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center">No staff designations found.</td></tr>
                @endforelse
            </tbody>
        </table>
         <div class="mt-3">
            {{ $designations->links() }}
        </div>
    </div>
</div>
@stop