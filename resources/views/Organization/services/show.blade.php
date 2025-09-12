@extends('layouts.app')

@section('title', 'Build Service')

@section('content_header')
    <h1>Build Service: {{ $service->name }}</h1>
@stop

@section('css')
@parent
<style>
    /* Styles for the task list */
    .task-list-table {
        margin-bottom: 0;
    }
    .task-list-table td {
        vertical-align: middle;
        padding: 0.75rem;
        border-top: 1px solid #f1f1f1;
    }
    .task-list-table tr:first-child td {
        border-top: none;
    }
    .task-row:hover td {
        background-color: #f8f9fa;
    }

    /* Styles for modals */
    #taskModal .modal-body {
        background-color: #f8f9fa;
    }
    #taskModal .form-group label {
        font-weight: 600 !important;
        color: #495057;
    }
    #taskModal .form-control {
        border-radius: 0.3rem;
        border: 1px solid #ced4da;
    }
    #taskModal .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
    }
    #taskModal .modal-header .close {
        font-size: 1.75rem;
        opacity: 0.8;
    }

    .custom-switch.custom-switch-lg .custom-control-label {
        padding-left: 3rem;
        padding-bottom: 1.5rem;
        line-height: 1.5rem;
        cursor: pointer;
    }
    .custom-switch.custom-switch-lg .custom-control-label::before {
        height: 1.5rem;
        width: 3rem;
        border-radius: 3rem;
        cursor: pointer;
    }
    .custom-switch.custom-switch-lg .custom-control-label::after {
        width: calc(1.5rem - 4px);
        height: calc(1.5rem - 4px);
        border-radius: 3rem;
        cursor: pointer;
    }
    .custom-switch.custom-switch-lg .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(calc(3rem - 1.5rem));
    }
</style>
@stop

@section('content')

<div id="alert-container"></div>

@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

{{-- Main Service Card --}}
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Service Details</h3>
        <div class="card-tools">
            <a href="{{ route('services.edit', $service->id) }}" class="btn btn-sm btn-light border text-warning">
                <i class="fas fa-pencil-alt"></i> Edit Service Details
            </a>
        </div>
    </div>
    <div class="card-body">
        <p>{{ $service->description ?? 'No description provided.' }}</p>
    </div>
</div>

{{-- Tasks Section --}}
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Tasks</h3>
        <div class="card-tools">
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#taskModal" data-action="create">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover task-list-table">
             <thead>
                <tr>
                    <th>Task Name</th>
                    <th style="width: 200px;" class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="task-list-body">
                @forelse($service->tasks as $task)
                    <tr class="task-row" id="task-row-{{ $task->id }}">
                        <td class="task-name-cell pl-3">{{ $task->name }}</td>
                        <td class="text-right pr-3">
                            <button class="btn btn-sm btn-light border" data-toggle="modal" data-target="#taskModal" data-action="edit" data-task='@json($task)'>
                                <i class="fas fa-edit text-warning"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-light border delete-task-btn ml-2" data-task-id="{{ $task->id }}">
                                <i class="fas fa-trash text-danger"></i> Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center text-muted py-4">No tasks have been added to this service yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@include('Organization.services._task_modal', ['designations' => $designations])

@stop

@section('js')
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    function showAlert(message, type = 'success') {
        const alertHtml = `<div class="alert alert-${type} alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>${message}</div>`;
        $('#alert-container').html(alertHtml).find('.alert').fadeIn();
        setTimeout(() => $('#alert-container').find('.alert').fadeOut(() => $(this).remove()), 5000);
    }

    // --- TASK MODAL & DELETE ---
    $('#taskModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        if (button.length === 0) return;
        event.stopPropagation();
        var action = button.data('action');
        var modal = $(this);
        var form = modal.find('form');
        form[0].reset();
        form.off('submit');
        $('#is_recurring').prop('checked', false).trigger('change');

        if (action === 'edit') {
            var task = button.data('task');
            modal.find('.modal-title').text('Edit Task');
            $('#task-name').val(task.name);
            $('#task-description').val(task.description);
            $('#task-start').val(task.start ? task.start.slice(0, 16) : '');
            $('#task-end').val(task.end ? task.end.slice(0, 16) : '');
            if(task.is_recurring) {
                $('#is_recurring').prop('checked', true).trigger('change');
                $('#recurring_frequency').val(task.recurring_frequency);
            }

            form.on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/organization/tasks/' + task.id, type: 'PUT', data: $(this).serialize(),
                    success: (response) => location.reload(),
                    error: (xhr) => alert('Error: ' + xhr.responseJSON.message)
                });
            });

        } else { // Create
            modal.find('.modal-title').text('Add New Task');
            form.on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/organization/services/{{ $service->id }}/tasks', type: 'POST', data: $(this).serialize(),
                    success: (response) => location.reload(),
                    error: (xhr) => alert('Error: ' + xhr.responseJSON.message)
                });
            });
        }
    });
    
    $('#is_recurring').on('change', function() {
        if ($(this).is(':checked')) { $('#recurring-options').slideDown(); } 
        else { $('#recurring-options').slideUp(); }
    }).trigger('change');

    $(document).on('click', '.delete-task-btn', function(e) {
        e.preventDefault(); e.stopPropagation();
        if (!confirm('Are you sure you want to delete this task?')) return;
        var taskId = $(this).data('task-id');
        $.ajax({
            url: '/organization/tasks/' + taskId, type: 'DELETE',
            success: (response) => {
                $('#task-row-' + taskId).remove();
                showAlert(response.message);
                 if ($('#task-list-body').children().length === 0) {
                    $('#task-list-body').html('<tr><td colspan="2" class="text-center text-muted py-4">No tasks have been added to this service yet.</td></tr>');
                }
            },
            error: (xhr) => alert('Error: ' + xhr.responseJSON.message)
        });
    });

});
</script>
@stop