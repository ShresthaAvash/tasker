@extends('layouts.app')

@section('title', 'Add Designation')

@section('content_header')
    <h1>Add New Staff Designation</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('staff-designations.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">Designation Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g., Senior Developer" required>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
            <button type="submit" class="btn btn-primary">Save Designation</button>
            <a href="{{ route('staff-designations.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop