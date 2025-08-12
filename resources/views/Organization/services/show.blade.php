@extends('layouts.app')

@section('title', 'Build Service')

@section('content_header')
    <h1>Build Service: {{ $service->name }}</h1>
@stop

@section('content')

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if(session('info')) <div class="alert alert-info">{{ session('info') }}</div> @endif

{{-- Main Service Card --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Service Details</h3>
        <div class="card-tools">
            <a href="{{ route('services.edit', $service->id) }}" class="btn btn-sm btn-warning">Edit Service Details</a>
        </div>
    </div>
    <div class="card-body">
        <p>{{ $service->description ?? 'No description provided.' }}</p>
    </div>
</div>

{{-- Jobs Section --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Jobs</h3>
        <div class="card-tools">
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#jobModal" data-action="create">Add Job</button>
        </div>
    </div>
    <div class="card-body">
        @forelse($service->jobs as $job)
            <div class="card card-outline card-info mb-3">
                <div class="card-header">
                    <h3 class="card-title">{{ $job->name }}</h3>
                    <div class="card-tools">
                        <form action="{{ route('jobs.assignTasks', $job) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-xs btn-success">Activate Assigned Tasks</button>
                        </form>
                        <a href="{{ route('jobs.edit', $job->id) }}" class="btn btn-xs btn-warning">Edit Job & Assign</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Task Name</th>
                                <th>Status</th>
                                <th>Assigned To</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($job->tasks as $task)
                                <tr>
                                    <td>{{ $task->name }}</td>
                                    <td>
                                        @if($task->status == 'not_started')
                                            <span class="badge badge-secondary">Not Started</span>
                                        @elseif($task->status == 'active')
                                            <span class="badge badge-primary">Active</span>
                                        @elseif($task->status == 'inactive')
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $task->staff->name ?? 'Not Assigned' }}</td>
                                    <td>
                                        <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this task?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No tasks yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="text-center text-muted">No jobs yet. Click "Add Job" to get started.</p>
        @endforelse
    </div>
</div>

@include('Organization.services._job_modal')

@stop

@section('js')
<script>
$(document).ready(function() {
    // --- THIS IS THE FIX ---
    // This JavaScript correctly sets the form's URL when the modal opens.
    $('#jobModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var modal = $(this);
        var form = modal.find('form');

        // Reset the form for the 'create' case
        form[0].reset();
        modal.find('input[name="_method"]').val('POST');
        
        // --- The 'create' action sets the correct URL for storing a new job ---
        modal.find('.modal-title').text('Add New Job');
        form.attr('action', '{{ route('services.jobs.store', $service) }}');
    });
});
</script>
@stop