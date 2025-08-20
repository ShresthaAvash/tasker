@extends('layouts.app')

@section('title', 'My Tasks')
@section('plugins.Select2', true)
@section('page_title', 'My Tasks')

@section('css')
<style>
    /* --- THIS IS THE NEW CSS FOR ACCORDION --- */
    .accordion .card-header {
        padding: 0;
    }

    .accordion-toggle-link {
        display: block;
        text-decoration: none !important;
        color: #495057;
        transition: background-color 0.2s ease-in-out;
    }

    .accordion-toggle-link:hover {
        background-color: #f8f9fa;
        color: #343a40;
    }

    .accordion-toggle-link.bg-info:hover {
        background-color: #138496 !important; /* A darker shade of info */
    }

    .accordion-toggle-link .collapse-icon {
        transition: transform 0.3s ease;
    }

    .accordion-toggle-link[aria-expanded="true"] .collapse-icon {
        transform: rotate(-180deg);
    }
</style>
@stop

@section('page-content')
<div class="card" id="task-manager-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Task List</h3>
        <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-sm btn-outline-primary active" id="client-view-btn">
                <input type="radio" name="view_type" value="client" checked> Client View
            </label>
            <label class="btn btn-sm btn-outline-primary" id="time-view-btn">
                <input type="radio" name="view_type" value="time"> Time View
            </label>
        </div>
    </div>
    <div class="card-body">
        <div id="status-update-feedback" class="alert" style="display: none;"></div>

        <!-- Filter and Search Row -->
        <div class="row mb-3 align-items-center">
            <div class="col-md-3">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Client, Service, Job or Task...">
            </div>
            <div class="col-md-3">
                <select id="status-filter" class="form-control" multiple="multiple" style="width: 100%;"></select>
            </div>
            <div class="col-md-4">
                <div id="dropdown-filters" class="row">
                    <div class="col">
                        <select id="year-filter" class="form-control">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $year == $startDate->year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <select id="month-filter" class="form-control">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ $num == $startDate->month ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="custom-range-filters" class="row" style="display: none;">
                    <div class="col">
                        <input type="date" id="start-date-filter" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col">
                        <input type="date" id="end-date-filter" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="col-md-2 d-flex justify-content-end">
                 <div class="custom-control custom-switch mr-3 pt-1">
                    <input type="checkbox" class="custom-control-input" id="custom-search-switch">
                    <label class="custom-control-label" for="custom-search-switch">Custom</label>
                </div>
                <button class="btn btn-secondary" id="reset-filters">Reset</button>
            </div>
        </div>

        <div id="task-view-container">
            @include('Staff.tasks._client_view', ['clientTaskGroups' => $clientTaskGroups, 'personalTasks' => $personalTasks, 'allStatuses' => $allStatuses])
        </div>
    </div>
</div>

@include('Staff.tasks._manual_time_modal')
@stop

@section('page_content_js')
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    let debounceTimer;
    const taskManager = $('#task-manager-card');
    window.taskTimers = {};

    $('#status-filter').select2({
        placeholder: 'Filter by Status',
        data: [
            { id: 'to_do', text: 'To Do' },
            { id: 'ongoing', text: 'Ongoing' },
            { id: 'completed', text: 'Completed' }
        ]
    });

    $('#status-filter').val(['to_do', 'ongoing']).trigger('change');

    function formatTime(totalSeconds) {
        if (isNaN(totalSeconds) || totalSeconds < 0) totalSeconds = 0;
        const hours = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
        const minutes = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
        const seconds = (totalSeconds % 60).toString().padStart(2, '0');
        return `${hours}:${minutes}:${seconds}`;
    }

    function renderTimerUI(taskRow) {
        const taskId = taskRow.data('task-id');
        const status = taskRow.data('status');
        const duration = parseInt(taskRow.data('duration'), 10) || 0;
        const timerStartedAt = taskRow.data('timer-started-at');
        const displayContainer = taskRow.find('.timer-display-container');
        const actionsContainer = taskRow.find('.timer-actions-container');

        if (window.taskTimers[taskId]) {
            clearInterval(window.taskTimers[taskId]);
            delete window.taskTimers[taskId];
        }
        displayContainer.empty();
        actionsContainer.empty();

        let buttonHtml = '';
        let displayHtml = `<span class="timer-display">${formatTime(duration)}</span>`;

        if (status === 'ongoing') {
            if (timerStartedAt) {
                buttonHtml += `<button class="btn btn-danger btn-xs stop-timer-btn" title="Stop Timer" data-task-id="${taskId}"><i class="fas fa-stop"></i></button>`;
                const startTime = new Date(timerStartedAt).getTime();
                const updateLiveTime = () => {
                    const now = new Date().getTime();
                    const elapsed = Math.floor((now - startTime) / 1000);
                    taskRow.find('.timer-display').text(formatTime(duration + elapsed));
                };
                updateLiveTime();
                window.taskTimers[taskId] = setInterval(updateLiveTime, 1000);
            } else {
                buttonHtml += `<button class="btn btn-success btn-xs start-timer-btn" title="Start Timer" data-task-id="${taskId}"><i class="fas fa-play"></i></button>`;
            }
        }
        
        const manualTimeButton = `
            <button class="btn btn-outline-secondary btn-xs add-manual-time-btn" 
                    title="Add Manual Time"
                    data-toggle="modal" 
                    data-target="#manualTimeModal">
                <i class="fas fa-plus"></i>
            </button>`;
        
        buttonHtml += manualTimeButton;

        displayContainer.html(displayHtml);
        actionsContainer.html(buttonHtml);
    }

    function initializeAllTimers() {
        Object.values(window.taskTimers).forEach(clearInterval);
        window.taskTimers = {};
        taskManager.find('tr[data-task-id]').each(function() {
            renderTimerUI($(this));
        });
    }

    function fetchTasks(page = 1) {
        clearTimeout(debounceTimer);
        const search = taskManager.find('#search-input').val();
        const viewType = taskManager.find('input[name="view_type"]:checked').val();
        const statuses = $('#status-filter').val();

        let data = { 
            search: search, 
            view_type: viewType, 
            page: page,
            statuses: statuses
        };

        if ($('#custom-search-switch').is(':checked')) {
            data.use_custom_range = 'true';
            data.start_date = taskManager.find('#start-date-filter').val();
            data.end_date = taskManager.find('#end-date-filter').val();
        } else {
            data.use_custom_range = 'false';
            data.year = taskManager.find('#year-filter').val();
            data.month = taskManager.find('#month-filter').val();
        }
        taskManager.find('#task-view-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('staff.tasks.index') }}", data: data,
            success: function(response) {
                taskManager.find('#task-view-container').html(response);
                initializeAllTimers();
            },
            error: function(xhr) {
                console.error(xhr);
                taskManager.find('#task-view-container').html('<div class="alert alert-danger">Failed to load tasks.</div>');
            }
        });
    }
    
    initializeAllTimers();
    
    taskManager.on('click', '#client-view-btn, #time-view-btn', () => setTimeout(fetchTasks, 50));
    
    taskManager.on('change', '#custom-search-switch', function() {
        $('#dropdown-filters').toggle(!this.checked);
        $('#custom-range-filters').toggle(this.checked);
        fetchTasks(1);
    });

    taskManager.on('keyup change', '#search-input, #start-date-filter, #end-date-filter, #year-filter, #month-filter, #status-filter', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchTasks(1), 500);
    });

    taskManager.on('click', '#reset-filters', () => {
        const now = new Date();
        $('#search-input').val('');
        $('#status-filter').val(['to_do', 'ongoing']).trigger('change');
        $('#custom-search-switch').prop('checked', false).trigger('change');
        $('#year-filter').val(now.getFullYear());
        $('#month-filter').val(now.getMonth() + 1);
    });

    taskManager.on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = new URL($(this).attr('href')).searchParams.get('page');
        fetchTasks(page);
    });

    taskManager.on('change', '.task-status-select', function() {
        const select = $(this);
        const taskRow = select.closest('tr[data-task-id]');
        if (taskRow.data('timer-started-at')) {
            alert('Please stop the timer before changing the task status.');
            select.val(taskRow.data('status')); 
            return;
        }
        $.ajax({
            type: 'PATCH',
            url: `/staff/tasks/${select.data('task-id')}/status`,
            data: { status: select.val(), instance_date: select.data('instance-date') },
            success: (response) => fetchTasks($('.pagination .active a').text() || 1),
            error: (xhr) => {
                alert(xhr.responseJSON?.error || 'An error occurred.');
                select.val(taskRow.data('status'));
            }
        });
    });

    taskManager.on('click', '.start-timer-btn', function() {
        const button = $(this);
        const taskId = button.data('task-id');
        const taskRow = button.closest('tr');
        const taskName = taskRow.data('task-name');

        $.ajax({
            type: 'POST',
            url: `/staff/tasks/${taskId}/start-timer`,
            success: (response) => {
                localStorage.setItem('runningTimer', JSON.stringify({
                    taskId: taskId,
                    taskName: taskName,
                    duration: response.duration_in_seconds,
                    startedAt: response.timer_started_at
                }));
                if (typeof window.renderGlobalTracker === 'function') {
                    window.renderGlobalTracker();
                }
                fetchTasks($('.pagination .active a').text() || 1);
            },
            error: (xhr) => alert(xhr.responseJSON?.error || 'Could not start timer.')
        });
    });

    taskManager.on('click', '.stop-timer-btn', function() {
        const taskId = $(this).data('task-id');
        $.ajax({
            type: 'POST',
            url: `/staff/tasks/${taskId}/stop-timer`,
            success: (response) => {
                localStorage.removeItem('runningTimer');
                $('#global-live-tracker').remove();
                fetchTasks($('.pagination .active a').text() || 1);
            },
            error: (xhr) => alert(xhr.responseJSON?.error || 'Could not stop timer.')
        });
    });

    $('#manualTimeModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const taskRow = button.closest('tr');
        const taskId = taskRow.data('task-id');
        const taskName = taskRow.data('task-name');

        const form = $('#manualTimeForm');
        form.attr('action', `/staff/tasks/${taskId}/add-manual-time`);
        $('#manual-time-task-name').text(taskName);
        $('#manual_hours').val(0);
        $('#manual_minutes').val(0);
        $('#manual-time-feedback').hide().text('');
    });

    $('#manualTimeForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const feedbackDiv = $('#manual-time-feedback');
        
        submitBtn.prop('disabled', true).text('Adding...');

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                $('#manualTimeModal').modal('hide');
                
                const taskId = form.attr('action').split('/')[3];
                const taskRow = $(`tr[data-task-id="${taskId}"]`);
                
                taskRow.data('duration', response.new_duration);
                renderTimerUI(taskRow);
                
                const runningTimerData = JSON.parse(localStorage.getItem('runningTimer'));
                if (runningTimerData && runningTimerData.taskId === taskId) {
                    runningTimerData.duration = response.new_duration;
                    localStorage.setItem('runningTimer', JSON.stringify(runningTimerData));
                    if (typeof window.renderGlobalTracker === 'function') {
                        window.renderGlobalTracker();
                    }
                }
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.error || 'An unexpected error occurred.';
                feedbackDiv.text(errorMsg).show();
            },
            complete: function() {
                submitBtn.prop('disabled', false).text('Add Time');
            }
        });
    });
});
</script>
@stop
