<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Due Date</th>
                <th>Task Name</th>
                <th>Client / Service / Job</th>
                <th style="width: 170px;">Status</th>
                <th style="width: 220px;" class="text-right">Time Tracker</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($paginatedTasks) && $paginatedTasks->isNotEmpty())
                @foreach($paginatedTasks as $task)
                <tr>
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
                        <select class="form-control form-control-sm task-status-select" data-task-id="{{ ($task->is_personal ?? false) ? 'p_' : 'a_' }}{{ $task->id }}">
                        @foreach($allStatuses as $key => $value)
                            <option value="{{ $key }}" {{ $task->status == $key ? 'selected' : '' }}>{{ $value }}</option>
                        @endforeach
                        </select>
                    </td>
                    <td class="text-right">
                        <div class="timer-button-group" style="{{ $task->status !== 'ongoing' ? 'display: none;' : '' }}">
                            <span class="timer-display font-weight-bold mr-2">{{ gmdate('H:i:s', $task->duration_in_seconds) }}</span>
                            <button class="btn btn-xs btn-success start-timer-btn" data-task-id="{{ ($task->is_personal ?? false) ? 'p_' : 'a_' }}{{ $task->id }}" style="{{ $task->timer_started_at ? 'display: none;' : '' }}"><i class="fas fa-play"></i></button>
                            <button class="btn btn-xs btn-danger stop-timer-btn" data-task-id="{{ ($task->is_personal ?? false) ? 'p_' : 'a_' }}{{ $task->id }}" style="{{ !$task->timer_started_at ? 'display: none;' : '' }}"><i class="fas fa-stop"></i></button>
                            <div class="btn-group ml-1">
                                <button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown"><i class="fas fa-plus"></i></button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item manual-time-btn" href="#" data-task-id="{{ ($task->is_personal ?? false) ? 'p_' : 'a_' }}{{ $task->id }}">Add Manual Time</a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center text-muted">No tasks found for the selected criteria.</td>
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