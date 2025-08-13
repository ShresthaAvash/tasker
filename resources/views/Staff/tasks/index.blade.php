@extends('layouts.app')

@section('title', 'My Tasks')

@section('content_header')
    <h1>My Tasks</h1>
@stop

@section('content')
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
            <div class="col-md-4">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Client, Service, Job or Task...">
            </div>
            <div class="col-md-6">
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

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;
    const taskManager = $('#task-manager-card');
    let globalTimerInterval;

    // --- HELPER FUNCTIONS ---
    function formatTime(totalSeconds) {
        if (isNaN(totalSeconds) || totalSeconds < 0) totalSeconds = 0;
        const hours = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
        const minutes = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
        const seconds = Math.floor(totalSeconds % 60).toString().padStart(2, '0');
        return `${hours}:${minutes}:${seconds}`;
    }

    function startGlobalTimer(taskId, taskName, initialSeconds, startTime) {
        clearInterval(globalTimerInterval);
        let timerBar = $('#global-timer-bar');
        
        if (!timerBar.length) {
            const timerHtml = `
                <li class="nav-item">
                    <div id="global-timer-bar" class="d-flex align-items-center bg-warning p-2 rounded" style="display:none;">
                        <i class="fas fa-clock fa-spin mr-2"></i>
                        <span id="global-timer-task-name" class="font-weight-bold mr-3"></span>
                        <span id="global-timer-display" class="mr-3"></span>
                        <button id="global-timer-stop-btn" class="btn btn-xs btn-danger">Stop</button>
                    </div>
                </li>`;
            $('nav.main-header .navbar-nav.ml-auto').prepend(timerHtml);
            timerBar = $('#global-timer-bar');
        }

        const secondsSinceStart = Math.floor((new Date() - new Date(startTime)) / 1000);
        let currentTotalSeconds = parseInt(initialSeconds, 10) + secondsSinceStart;

        timerBar.data('task-id', taskId);
        timerBar.find('#global-timer-task-name').text(taskName);
        timerBar.find('#global-timer-display').text(formatTime(currentTotalSeconds));
        timerBar.show();

        globalTimerInterval = setInterval(() => {
            currentTotalSeconds++;
            timerBar.find('#global-timer-display').text(formatTime(currentTotalSeconds));
        }, 1000);
    }

    function stopGlobalTimer() {
        clearInterval(globalTimerInterval);
        $('#global-timer-bar').remove();
        fetchTasks($('.pagination .active a').text() || 1);
    }
    
    // --- MAIN AJAX FUNCTION ---
    function fetchTasks(page = 1) {
        clearTimeout(debounceTimer);
        const search = taskManager.find('#search-input').val();
        const viewType = taskManager.find('input[name="view_type"]:checked').val();
        
        let data = {
            _token: '{{ csrf_token() }}',
            search: search,
            view_type: viewType,
            page: page
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
            url: "{{ route('staff.tasks.index') }}",
            data: data,
            success: function(response) {
                taskManager.find('#task-view-container').html(response);
            },
            error: function(xhr) {
                console.error(xhr);
                alert('Could not load tasks. Please check the console for errors.');
                taskManager.find('#task-view-container').html('<div class="alert alert-danger">Failed to load tasks.</div>');
            }
        });
    }

    // --- INITIALIZE GLOBAL TIMER ON PAGE LOAD ---
    const activeTimerBar = $('#global-timer-bar');
    if (activeTimerBar.length && activeTimerBar.data('task-id')) {
        startGlobalTimer(
            activeTimerBar.data('task-id'),
            activeTimerBar.data('task-name'),
            activeTimerBar.data('initial-seconds'),
            activeTimerBar.data('start-time')
        );
    }

    // --- EVENT HANDLERS ---
    taskManager.on('click', '#client-view-btn, #time-view-btn', function() {
        setTimeout(fetchTasks, 50);
    });
    
    $('#custom-search-switch').on('change', function() {
        if ($(this).is(':checked')) {
            $('#dropdown-filters').hide();
            $('#custom-range-filters').show();
        } else {
            $('#dropdown-filters').show();
            $('#custom-range-filters').hide();
        }
        fetchTasks(1);
    });

    taskManager.on('keyup change', '#search-input, #start-date-filter, #end-date-filter, #year-filter, #month-filter', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchTasks(1), 500);
    });

    taskManager.on('click', '#reset-filters', function() {
        const now = new Date();
        $('#search-input').val('');
        $('#custom-search-switch').prop('checked', false).trigger('change');
        $('#year-filter').val(now.getFullYear());
        $('#month-filter').val(now.getMonth() + 1);
        fetchTasks(1);
    });

    taskManager.on('change', '.task-status-select', function() {
        const select = $(this);
        const taskId = select.data('task-id');
        const newStatus = select.val();
        const row = select.closest('tr');

        $.ajax({
            type: 'PATCH',
            url: `/staff/tasks/${taskId}/status`,
            data: { _token: '{{ csrf_token() }}', status: newStatus },
            success: function(response) {
                if (newStatus === 'ongoing') {
                    row.find('.timer-button-group').show();
                } else {
                    row.find('.timer-button-group').hide();
                    if ($('#global-timer-bar').data('task-id') === taskId) {
                        stopGlobalTimer();
                    }
                }
                const feedback = $('#status-update-feedback').removeClass('alert-danger').addClass('alert-success');
                feedback.text(response.success).fadeIn();
                setTimeout(() => feedback.fadeOut(), 3000);
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'An error occurred.';
                const feedback = $('#status-update-feedback').removeClass('alert-success').addClass('alert-danger');
                feedback.text(errorMsg).fadeIn();
                setTimeout(() => feedback.fadeOut(), 3000);
            }
        });
    });

    taskManager.on('click', '.start-timer-btn', function() {
        const button = $(this);
        const taskId = button.data('task-id');
        if (!confirm('This will stop any other active timer. Start timer for this task?')) { return; }
        $.ajax({
            type: 'PATCH',
            url: `/staff/tasks/${taskId}/timer/start`,
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                $('.start-timer-btn').show();
                $('.stop-timer-btn').hide();
                button.hide();
                button.siblings('.stop-timer-btn').show();
                startGlobalTimer(response.task_id, response.task_name, response.duration_in_seconds, response.timer_started_at);
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Could not start timer.';
                alert(errorMsg);
                console.error(xhr);
            }
        });
    });

    taskManager.on('click', '.stop-timer-btn', function() {
        const taskId = $(this).data('task-id');
        $.ajax({
            type: 'PATCH',
            url: `/staff/tasks/${taskId}/timer/stop`,
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) { stopGlobalTimer(); },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Could not stop timer.';
                alert(errorMsg);
                console.error(xhr);
            }
        });
    });

    $(document).on('click', '#global-timer-stop-btn', function() {
        const taskId = $('#global-timer-bar').data('task-id');
        $.ajax({
            type: 'PATCH',
            url: `/staff/tasks/${taskId}/timer/stop`,
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) { stopGlobalTimer(); },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Could not stop timer.';
                alert(errorMsg);
                console.error(xhr);
            }
        });
    });

    taskManager.on('click', '.manual-time-btn', function() {
        const taskId = $(this).data('task-id');
        const taskName = $(this).closest('tr').find('td:first').text();
        const form = $('#manualTimeForm');
        form.attr('action', `/staff/tasks/${taskId}/timer/manual`);
        $('#manual-time-task-name').text(taskName);
        form[0].reset();
        $('#manual-time-errors').text('');
        $('#manualTimeModal').modal('show');
    });

    $('#manualTimeForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(response) {
                $('#manualTimeModal').modal('hide');
                fetchTasks($('.pagination .active a').text() || 1);
            },
            error: function(response) {
                const errors = response.responseJSON.errors;
                let errorHtml = '';
                for (const key in errors) { errorHtml += `<p>${errors[key][0]}</p>`; }
                $('#manual-time-errors').html(errorHtml);
            }
        });
    });

    taskManager.on('click', '.pagination a', function(e) {
        e.preventDefault();
        const page = new URL($(this).attr('href')).searchParams.get('page');
        fetchTasks(page);
    });
});
</script>
@stop