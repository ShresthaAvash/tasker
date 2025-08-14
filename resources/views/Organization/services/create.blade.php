@extends('layouts.app')

@section('title', 'Add Service')

@section('content_header')
    <h1>Add New Service</h1>
@stop

@section('content')
<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">Service Details</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('services.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Service Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="A" selected>Active</option>
                    <option value="I">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-info">Save Service</button>
            <a href="{{ route('services.index') }}" class="btn btn-default">Cancel</a>
        </form>
    </div>
</div>
@stop