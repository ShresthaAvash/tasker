@extends('layouts.app')

@section('title', 'Suspended Services')

@section('content_header')
    <h1>Suspended Services</h1>
@stop

@section('content')
{{-- --- THIS IS THE FIX --- --}}
{{-- We add 'card-info' and 'card-outline' to style the card --}}
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title">All Suspended Services</h3>
    </div>
    <div class="card-body p-0"> {{-- Use p-0 for a seamless table --}}
        @if(session('success'))
            <div class="alert alert-success m-3">{{ session('success') }}</div>
        @endif

        {{-- --- THIS IS THE FIX --- --}}
        {{-- We remove 'table-bordered' for a cleaner look --}}
        <table class="table table-hover table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($services as $service)
                <tr>
                    <td>{{ $service->name }}</td>
                    <td>{{ Str::limit($service->description, 50, '...') }}</td>
                    <td><span class="badge badge-danger">Suspended</span></td>
                    <td>
                        <form action="{{ route('services.toggleStatus', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Activate this service?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-xs btn-success">Activate</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center">No suspended services found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($services->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-center">
            {{ $services->links() }}
        </div>
    </div>
    @endif
</div>
@stop