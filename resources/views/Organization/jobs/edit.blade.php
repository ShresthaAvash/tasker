@extends('layouts.app')

@section('title', 'Edit Job')

@section('content_header')
    <h1>Edit Job: {{ $job->name }}</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    {{-- Job Details Form --}}
    <form action="{{ route('jobs.update', $job->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-header"><h3 class="card-title">Job Details</h3></div>
        <div class="card-body">
            <div class="form-group">
                <label for="name">Job Name</label>
                <input type="text" name="name" class="form-control" value="{{ $job->name }}" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" class="form-control">{{ $job->description }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="{{ route('services.show', $job->service_id) }}" class="btn btn-secondary">Back to Service Builder</a>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Tasks</h3>
        <div class="card-tools">
            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#taskModal" data-action="create" data-jobid="{{ $job->id }}">Add Task</button>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Assigned To</th>
                    <th style="width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($job->tasks as $task)
                    <tr>
                        <td>{{ $task->name }}</td>
                        <td>
                            {{-- --- NEW ASSIGNMENT DROPDOWN --- --}}
                            <select class="form-control form-control-sm staff-assign-dropdown" data-task-id="{{ $task->id }}">
                                <option value="">-- Not Assigned --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ $task->staff_id == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="assign-status text-success" id="status-{{ $task->id }}" style="display:none;">Saved!</small>
                        </td>
                        <td>
                            <button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#taskModal" data-action="edit" data-task='{{ $task->toJson() }}'>Edit</button>
                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this task?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted">No tasks yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('Organization.services._task_modal')

@stop

@section('js')
<script>
$(document).ready(function() {

    $('#taskModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var modal = $(this);
        var form = modal.find('form');

        form[0].reset();
        $('#is_recurring').prop('checked', false);
        $('#recurring-options').hide();
        $('#task-end').prop('required', false);

        if (action === 'edit') {
            var task = button.data('task');
            modal.find('.modal-title').text('Edit Task');
            form.attr('action', '/organization/tasks/' + task.id);
            form.find('input[name="_method"]').val('PUT');

            $('#task-name').val(task.name);
            $('#task-description').val(task.description);
            $('#staff_designation_id').val(task.staff_designation_id);
            $('#task-start').val(task.start ? task.start.slice(0, 16).replace(' ', 'T') : '');
            $('#task-end').val(task.end ? task.end.slice(0, 16).replace(' ', 'T') : '');

            if(task.is_recurring) {
                $('#is_recurring').prop('checked', true);
                $('#recurring_frequency').val(task.recurring_frequency);
            }

        } else {
            var jobId = button.data('jobid');
            modal.find('.modal-title').text('Add New Task');
            form.attr('action', '/organization/jobs/' + jobId + '/tasks');
            form.find('input[name="_method"]').val('POST');
        }

        function toggleRecurringFields() {
            if ($('#is_recurring').is(':checked')) {
                $('#recurring-options').slideDown();
                $('#task-end').prop('required', true);
                $('#end-date-help').text("The date the recurrence will end.");
            } else {
                $('#recurring-options').slideUp();
                $('#task-end').prop('required', false);
                $('#end-date-help').text("For a non-recurring task, this is the task's duration (optional).");
            }
        }

        toggleRecurringFields();
        $('#is_recurring').off('change').on('change', toggleRecurringFields);
    });

    $('.staff-assign-dropdown').on('change', function() {
        var dropdown = $(this);
        var taskId = dropdown.data('taskId');
        var staffId = dropdown.val();
        var statusLabel = $('#status-' + taskId);

        $.ajax({
            url: '/organization/tasks/' + taskId + '/assign-staff',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                staff_id: staffId
            },
            success: function(response) {
                statusLabel.fadeIn();
                setTimeout(function() {
                    statusLabel.fadeOut();
                }, 2000);
            },
            error: function() {
                alert('Failed to assign staff. Please try again.');
            }
        });
    });

});
</script>
@stop