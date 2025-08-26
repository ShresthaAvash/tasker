@extends('layouts.app')

@section('title', 'Staff Designations')

@section('content_header')
    <h1>Staff Designations</h1>
@stop

@section('content')
{{-- The card now uses 'card-primary' and 'card-outline' for the blue theme --}}
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">All Designations</h3>
        <div class="card-tools">
            {{-- The button is now 'btn-primary' for the blue theme --}}
            <a href="{{ route('staff-designations.create') }}" class="btn btn-primary btn-sm">Add New Designation</a>
        </div>
    </div>
    <div class="card-body p-0">
        @if(session('success'))
            <div class="alert alert-success m-3">{{ session('success') }}</div>
        @endif

        <table class="table table-hover table-striped">
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
    </div>
    @if($designations->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $designations->links() }}
        </div>
    </div>
    @endif
</div>
@stop