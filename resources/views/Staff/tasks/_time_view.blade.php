<div class="table-responsive">
    <table class="table table-hover time-view-table">
        <thead>
            <tr>
                <th>Due Date</th>
                <th>Task Name</th>
                <th>Client / Service</th>
                <th style="width: 120px;">Time Logged</th>
                <th style="width: 170px;">Status</th>
                <th style="width: 150px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($paginatedTasks) && $paginatedTasks->isNotEmpty())
                @foreach($paginatedTasks as $task)
                    @php
                        $eventId = (($task->is_personal ?? false) ? 'p_' : 'a_') . $task->id;
                        if ($task->is_recurring) {
                            $eventId .= '_' . $task->due_date_instance->toDateString();
                        }
                    @endphp
                    <tr data-task-id="{{ $eventId }}"
                        data-assigned-task-id="{{ $task->id }}"
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
                                <small class="text-muted">{{ optional($task->service)->name }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="timer-display-container font-weight-bold">
                                {{-- This div is populated by JS with timer info --}}
                            </div>
                        </td>
                        <td>
                            <select class="form-control form-control-sm task-status-select" data-task-id="{{ $eventId }}" data-instance-date="{{ $task->due_date_instance->toDateString() }}">
                            @foreach($allStatuses as $key => $value)
                                <option value="{{ $key }}" {{ $task->status == $key ? 'selected' : '' }}>{{ $value }}</option>
                            @endforeach
                            </select>
                        </td>
                        <td>
                            <div class="d-flex">
                                <div class="timer-actions-container btn-group mr-1"></div>
                                @unless($task->is_personal ?? false)
                                <button class="btn btn-xs btn-outline-secondary open-notes-modal" title="Working Notes"><i class="fas fa-sticky-note"></i></button>
                                <button class="btn btn-xs btn-outline-info open-comments-modal ml-1" title="Comments"><i class="fas fa-comments"></i></button>
                                @endunless
                                <div class="btn-group ml-1">
                                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right">
                                        <a class="dropdown-item view-in-calendar-btn"
                                           href="{{ route('staff.calendar', ['event_id' => $eventId, 'date' => $task->due_date_instance->toDateString()]) }}">
                                            <i class="fas fa-fw fa-calendar-alt mr-2"></i>View in Calendar
                                        </a>
                                    </div>
                                </div>
                            </div>
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