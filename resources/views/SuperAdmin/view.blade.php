@extends('layouts.app')

@section('page-content')
    <h3>Organization Details</h3>

    <div class="card">
        <div class="card-body">
            <p><strong>Name:</strong> {{ $organization->name }}</p>
            <p><strong>Email:</strong> {{ $organization->email }}</p>
            <p><strong>Phone:</strong> {{ $organization->phone }}</p>
            <p><strong>Address:</strong> {{ $organization->address }}</p>
            <p><strong>Status:</strong> 
                @if ($organization->status === 'A')
                    <span class="badge bg-success">Active</span>
                @else
                    <span class="badge bg-danger">Inactive</span>
                @endif
            </p>
        </div>
    </div>
@endsection