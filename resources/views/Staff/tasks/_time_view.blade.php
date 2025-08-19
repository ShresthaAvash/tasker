<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Due Date</th>
                <th>Task Name</th>
                <th>Client / Service / Job</th>
                <th style="width: 200px;">Time Tracking</th>
                <th style="width: 170px;">Status</th>
                <th style="width: 150px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($paginatedTasks) && $paginatedTasks->isNotEmpty())
                @foreach($paginatedTasks as $task)
                <tr data-task-id="{{ ($task->is_personal ?? false) ? 'p_' : 'a_' }}{{ $task->id }}"
                    data-task-name="{{ $task->name }}"
                    data-status="{{ $task->status }}"
                    data-duration="{{ $task->duration_in_seconds ?? 0 }}"
                    data-timer-started-at="{{ $task->timer_started_at ? $task->timer_started_at->toIso8601String() : '' }}">
                    <td>{{ $task->due_date_instance->format('d M Y, h:i A') }}</td>
                    <td>{{ $task->name }}</td>
                    <td>
                        @if($task->is_personal ?? false)
                            <span class="badge badge-info">Personal Task</span>
                        @else
                            {{ optional($task->client)->name }} <br>
                            <small class="text-muted">{{ optional($task->service)->name }} / {{ optional($task->job)->name }}</small>
                        @endif
                    </td>
                    <td>
                        <div class="timer-controls d-flex align-items-center justify-content-between"></div>
                    </td>
                    <td>
                        <select class="form-control form-control-sm task-status-select" data-task-id="{{ ($task->is_personal ?? false) ? 'p_' : 'a_' }}{{ $task->id }}" data-instance-date="{{ $task->due_date_instance->toDateString() }}">
                        @foreach($allStatuses as $key => $value)
                            <option value="{{ $key }}" {{ $task->status == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-secondary btn-xs add-manual-time-btn"
                                data-toggle="modal"
                                data-target="#manualTimeModal">
                            <i class="fas fa-plus-circle"></i> Add Time
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="text-center text-muted">No tasks found for the selected criteria.</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

@if(isset($paginatedTasks) && $paginatedTasks->hasPages())
<div class="mt-3">
    {{ $paginatedTasks->links() }}
</div>
@endif