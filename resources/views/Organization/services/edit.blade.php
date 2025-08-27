@extends('layouts.app')

@section('title', 'Edit Service')

@section('content_header')
    {{-- FIX: Changed $job->name to $service->name --}}
    <h1>Edit Service: {{ $service->name }}</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">Service Details</h3>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FIX: Changed route to services.update and passed $service->id --}}
        <form action="{{ route('services.update', $service->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Service Name</label>
                {{-- FIX: Changed value to use $service->name --}}
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $service->name) }}" required>
                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                {{-- FIX: Changed value to use $service->description --}}
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror">{{ old('description', $service->description) }}</textarea>
                @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                {{-- FIX: Added status dropdown and set selected value from $service->status --}}
                <select name="status" id="status" class="form-control" required>
                    <option value="A" {{ old('status', $service->status) == 'A' ? 'selected' : '' }}>Active</option>
                    <option value="I" {{ old('status', $service->status) == 'I' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Service</button>
            {{-- FIX: Changed back link to go to services index --}}
            <a href="{{ route('services.index') }}" class="btn btn-default">Cancel</a>
        </form>
    </div>
</div>
@stop