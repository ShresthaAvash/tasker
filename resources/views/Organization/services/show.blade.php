@extends('layouts.app')

@section('title', 'Build Service')

@section('content_header')
    <h1>Build Service: {{ $service->name }}</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

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
                        <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#jobModal" data-action="edit" data-job='{{ $job->toJson() }}'>Edit</button>
                        <form action="{{ route('jobs.destroy', $job->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this job and all its tasks?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Tasks List --}}
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Task Name</th><th>Deadline</th><th>Assigned To</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            @forelse($job->tasks as $task)
                                <tr>
                                    <td>{{ $task->name }}</td>
                                    <td>{{ $task->deadline_offset }} {{ Str::plural($task->deadline_unit, $task->deadline_offset) }} after job starts</td>
                                    <td>{{ $task->designation->name ?? 'Not Assigned' }}</td>
                                    <td>
                                        <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#taskModal" data-action="edit" data-task='{{ $task->toJson() }}'>Edit</button>
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
                <div class="card-footer">
                    <button class="btn btn-xs btn-success" data-toggle="modal" data-target="#taskModal" data-action="create" data-jobid="{{ $job->id }}">Add Task</button>
                </div>
            </div>
        @empty
            <p class="text-center text-muted">No jobs yet. Click "Add Job" to get started.</p>
        @endforelse
    </div>
</div>

@include('Organization.services._job_modal')
@include('Organization.services._task_modal', ['designations' => $designations])

@stop

@section('js')
<script>
$(document).ready(function() {
    // Job Modal Logic
    $('#jobModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var modal = $(this);

        if (action === 'edit') {
            var job = button.data('job');
            modal.find('.modal-title').text('Edit Job');
            modal.find('form').attr('action', '/organization/jobs/' + job.id);
            modal.find('input[name="_method"]').val('PUT');
            modal.find('#job-name').val(job.name);
            modal.find('#job-description').val(job.description);
        } else {
            modal.find('.modal-title').text('Add New Job');
            modal.find('form').attr('action', '{{ route('services.jobs.store', $service) }}');
            modal.find('input[name="_method"]').val('POST');
            modal.find('form')[0].reset();
        }
    });

    // Task Modal Logic
    $('#taskModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var modal = $(this);

        if (action === 'edit') {
            var task = button.data('task');
            modal.find('.modal-title').text('Edit Task');
            modal.find('form').attr('action', '/organization/tasks/' + task.id);
            modal.find('input[name="_method"]').val('PUT');
            modal.find('#task-name').val(task.name);
            modal.find('#task-description').val(task.description);
            modal.find('#deadline_offset').val(task.deadline_offset);
            modal.find('#deadline_unit').val(task.deadline_unit);
            modal.find('#staff_designation_id').val(task.staff_designation_id);

            // --- JAVASCRIPT UPDATE START ---
            // Format and set the start/end dates for the datetime-local input fields
            // The 'T' is required by the datetime-local input format.
            modal.find('#task-start').val(task.start ? task.start.slice(0, 16).replace(' ', 'T') : '');
            modal.find('#task-end').val(task.end ? task.end.slice(0, 16).replace(' ', 'T') : '');
            // --- JAVASCRIPT UPDATE END ---

        } else {
            var jobId = button.data('jobid');
            modal.find('.modal-title').text('Add New Task');
            modal.find('form').attr('action', '/organization/jobs/' + jobId + '/tasks');
            modal.find('input[name="_method"]').val('POST');
            modal.find('form')[0].reset();
        }
    });
});
</script>
@stop