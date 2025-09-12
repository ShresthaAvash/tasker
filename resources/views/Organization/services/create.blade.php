@extends('layouts.app')

@section('title', 'Add Service')

@section('content_header')
    <h1>Add New Service</h1>
@stop

@section('content')
<div class="card card-primary">
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

            <hr>
            <h6 class="font-weight-bold">Scheduling & Recurrence</h6>
            <div class="form-group bg-light p-3 rounded border mb-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                    <label class="custom-control-label" for="is_recurring">Make this a recurring service</label>
                </div>
            </div>
            <div id="recurring-options" style="{{ old('is_recurring') ? '' : 'display: none;' }}" class="pl-3 ml-1 mb-4 border-left-info">
                 <div class="form-group">
                    <label for="recurring_frequency" class="font-weight-normal">Recurs Every...</label>
                    <select id="recurring_frequency" name="recurring_frequency" class="form-control">
                        <option value="daily" {{ old('recurring_frequency') == 'daily' ? 'selected' : '' }}>Day</option>
                        <option value="weekly" {{ old('recurring_frequency') == 'weekly' ? 'selected' : '' }}>Week</option>
                        <option value="monthly" {{ old('recurring_frequency') == 'monthly' ? 'selected' : '' }}>Month</option>
                        <option value="yearly" {{ old('recurring_frequency') == 'yearly' ? 'selected' : '' }}>Year</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Service</button>
            <a href="{{ route('services.index') }}" class="btn btn-default">Cancel</a>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('#is_recurring').on('change', function() {
            if ($(this).is(':checked')) {
                $('#recurring-options').slideDown();
            } else {
                $('#recurring-options').slideUp();
            }
        });
    });
</script>
@stop