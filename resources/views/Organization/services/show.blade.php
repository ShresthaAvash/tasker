@extends('layouts.app')

@section('title', 'Build Service')

@section('content_header')
    <h1>Build Service: {{ $service->name }}</h1>
@stop

@section('css')
<style>
    /*
     * Final styles for a spacious, professional, and clean UI.
     */
    .job-accordion .card {
        border: 1px solid #dee2e6;
        box-shadow: none;
    }

    .job-accordion .card-header {
        padding: 0;
        cursor: pointer;
        background-color: #f8f9fa;
        transition: background-color 0.2s ease-in-out;
        border-bottom: 1px solid #dee2e6;
    }

    .job-accordion .card-header:hover {
        background-color: #f1f1f1;
    }

    .job-accordion .card-header a {
        display: block;
        padding: 1rem 1.25rem;
        color: inherit;
        text-decoration: none;
    }

    .job-accordion .collapse-icon {
        transition: transform 0.3s ease;
        font-size: 0.9rem;
    }

    .job-accordion a[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }

    /* Styles for the task list inside the accordion */
    .task-list-table {
        margin-bottom: 0;
    }
    .task-list-table td {
        vertical-align: middle;
        padding: 1rem 1.25rem;
        border-top: 1px solid #f1f1f1;
    }
    .task-list-table tr:first-child td {
        border-top: none;
    }
    
    .task-row:hover td {
        background-color: #f8f9fa;
    }
    .card-tools .btn {
        margin-left: 0.5rem;
    }

    .border-left-info {
        border-left: 3px solid #17a2b8 !important;
    }

    /* Modern Modal Styles */
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

    /* Larger, Modern Toggle Switch */
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
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Service Details</h3>
        <div class="card-tools">
            <a href="{{ route('services.edit', $service->id) }}" class="btn btn-sm btn-light border text-warning">
                <i class="fas fa-pencil-alt"></i> Edit Service
            </a>
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
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#jobModal" data-action="create">
                <i class="fas fa-plus"></i> Add Job
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="accordion job-accordion" id="jobsAccordion">
            @forelse($service->jobs as $job)
                @include('Organization.services._job_accordion_item', ['job' => $job])
            @empty
                <p id="no-jobs-message" class="text-center text-muted">No jobs yet. Click "Add Job" to get started.</p>
            @endforelse
        </div>
    </div>
</div>

@include('Organization.services._job_modal')
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

    // --- JOB EDIT & DELETE BUTTONS ---
    // Handle opening the modal via a delegated event
    $(document).on('click', '[data-action="edit"][data-target="#jobModal"]', function(e) {
        e.stopPropagation(); // Prevent accordion from toggling
        
        var button = $(this);
        var job = button.data('job');
        var modal = $('#jobModal');
        var form = modal.find('form');

        form[0].reset();
        form.off('submit');

        modal.find('.modal-title').text('Edit Job');
        modal.find('#job-name').val(job.name);
        modal.find('#job-description').val(job.description);

        form.on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '/organization/jobs/' + job.id,
                type: 'PUT',
                data: $(this).serialize(),
                success: function(response) {
                    $('#job-title-' + job.id).text(response.job.name);
                    var card = $('#job-card-' + job.id);
                    var currentData = card.data('job');
                    currentData.name = response.job.name;
                    currentData.description = response.job.description;
                    card.data('job', currentData);
                    modal.modal('hide');
                    showAlert(response.message);
                },
                error: (xhr) => alert('Error: ' + xhr.responseJSON.message)
            });
        });
        modal.modal('show');
    });

    // Handle Job Deletion
    $(document).on('click', '.delete-job-btn', function(e) {
        e.preventDefault(); e.stopPropagation();
        if (!confirm('Are you sure you want to delete this job and all its tasks? This action cannot be undone.')) return;
        var jobId = $(this).data('job-id');
        $.ajax({
            url: '/organization/jobs/' + jobId, type: 'DELETE',
            success: (response) => {
                $('#job-card-' + jobId).fadeOut(300, function() { $(this).remove(); });
                showAlert(response.message);
            },
            error: (xhr) => alert('Error: ' + xhr.responseJSON.message)
        });
    });

    // Job Modal (for "Add Job" button)
    $('#jobModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        if (action === 'edit') return; // Handled by delegated click event above

        var modal = $(this);
        var form = modal.find('form');
        form[0].reset();
        form.off('submit');
        modal.find('.modal-title').text('Add New Job');

        form.on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route("services.jobs.store", $service) }}',
                type: 'POST',
                data: $(this).serialize(),
                success: (response) => location.reload(),
                error: (xhr) => alert('Error: ' + xhr.responseJSON.message)
            });
        });
    });

    // Task Modal Logic (with stopPropagation fix)
    $('#taskModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        if (button.length === 0) return; // Prevent errors on programmatic show
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
                    url: '/organization/tasks/' + task.id,
                    type: 'PUT',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#task-row-' + task.id + ' .task-name-cell').text(response.task.name);
                        modal.modal('hide');
                        showAlert(response.message);
                    },
                    error: (xhr) => alert('Error: ' + xhr.responseJSON.message)
                });
            });

        } else { // Create
            var jobId = button.data('jobid');
            modal.find('.modal-title').text('Add New Task');
            form.on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/organization/jobs/' + jobId + '/tasks',
                    type: 'POST',
                    data: $(this).serialize(),
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

    // Handle Task Deletion
    $(document).on('click', '.delete-task-btn', function(e) {
        e.preventDefault(); e.stopPropagation();
        if (!confirm('Are you sure you want to delete this task?')) return;
        var taskId = $(this).data('task-id');
        $.ajax({
            url: '/organization/tasks/' + taskId, type: 'DELETE',
            success: (response) => {
                $('#task-row-' + taskId).remove();
                showAlert(response.message);
            },
            error: (xhr) => alert('Error: ' + xhr.responseJSON.message)
        });
    });

});
</script>
@stop