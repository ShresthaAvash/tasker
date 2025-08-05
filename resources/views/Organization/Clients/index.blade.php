@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Clients</h1>
    <a href="{{ route('clients.create') }}" class="btn btn-primary mb-3">Add New Client</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr>
                <td>{{ $client->name }}</td>
                <td>{{ $client->email }}</td>
                <td>{{ $client->phone }}</td>
                <td>{{ $client->status == 'A' ? 'Active' : 'Inactive' }}</td>
                <td>
                    @if($client->photo)
                        <img src="{{ asset('storage/'.$client->photo) }}" alt="Photo" width="50">
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="6">No clients found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
