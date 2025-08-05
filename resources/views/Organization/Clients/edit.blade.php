@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Client</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clients.update', $client->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Client Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $client->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Client Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $client->email) }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Client Phone</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $client->phone) }}">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Client Address</label>
            <textarea class="form-control" id="address" name="address">{{ old('address', $client->address) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="photo" class="form-label">Client Photo</label>
            @if($client->photo)
                <div>
                    <img src="{{ asset('storage/'.$client->photo) }}" alt="Photo" width="100" class="mb-2">
                </div>
            @endif
            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status" required>
                <option value="A" {{ old('status', $client->status) == 'A' ? 'selected' : '' }}>Active</option>
                <option value="I" {{ old('status', $client->status) == 'I' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password (leave blank to keep current)</label>
            <input type="password" class="form-control" id="password" name="password" >
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" >
        </div>

        <button type="submit" class="btn btn-primary">Update Client</button>
    </form>
</div>
@endsection
