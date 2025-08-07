@extends('layouts.app')

@section('title', 'Edit Designation')

@section('content_header')
    <h1>Edit Staff Designation</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('staff-designations.update', $staffDesignation->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="name">Designation Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $staffDesignation->name) }}" required>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Update Designation</button>
            <a href="{{ route('staff-designations.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop