@extends('layouts.app')

@section('title', 'Edit Job')

@section('content_header')
    <h1>Edit Job: {{ $job->name }}</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- Card for Job Details --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Job Details</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('jobs.update', $job->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Job Name</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $job->name) }}" required>
                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $job->description) }}</textarea>
                @error('description') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('services.show', $job->service_id) }}" class="btn btn-secondary">Back to Service Builder</a>
        </form>
    </div>
</div>

{{-- ✅ ADDED: Card for Managing Tasks --}}
<div class="card card-outline card-info mt-4">
    <div class="card-header">
        <h3 class="card-title">Tasks</h3>
        <div class="card-tools">
            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#taskModal" data-action="create" data-jobid="{{ $job->id }}">Add Task</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Deadline</th>
                    <th>Assigned To</th>
                    <th style="width: 120px">Actions</th>
                </tr>
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
</div>

{{-- Include the reusable task modal --}}
@include('Organization.services._task_modal', ['designations' => $designations])

@stop

@section('js')
<script>
$(document).ready(function() {
    // Task Modal Logic (copied from services.show)
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