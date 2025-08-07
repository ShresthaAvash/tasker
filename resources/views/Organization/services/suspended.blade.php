@extends('layouts.app')

@section('title', 'Suspended Services')

@section('content_header')
    <h1>Suspended Services</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Suspended Services</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <table class="table table-bordered table-striped">
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
                        {{-- THIS IS THE ACTIVATE BUTTON --}}
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
        <div class="mt-3">
            {{ $services->links() }}
        </div>
    </div>
</div>
@stop