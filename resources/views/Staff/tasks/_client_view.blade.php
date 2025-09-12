<div class="accordion" id="client-accordion">
    {{-- Personal Tasks Section --}}
    @if($personalTasks->isNotEmpty())
    <div class="card mb-2 shadow-sm">
        <div class="card-header p-0" id="heading-personal">
             <a href="#collapse-personal" class="d-flex justify-content-between align-items-center p-3 accordion-toggle-link bg-info text-white" data-toggle="collapse" aria-expanded="true">
                <span class="font-weight-bold"><i class="fas fa-user mr-2"></i> Personal Tasks</span>
                <i class="fas fa-chevron-down collapse-icon"></i>
            </a>
        </div>
        <div id="collapse-personal" class="collapse show">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 45%;">Task</th>
                            <th style="width: 20%;">Due Date</th>
                            <th style="width: 10%;">Time Logged</th>
                            <th style="width: 15%;">Status</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($personalTasks as $task)
                        @php
                            $eventId = 'p_' . $task->id;
                            if ($task->is_recurring) {
                                $eventId .= '_' . $task->due_date_instance->toDateString();
                            }
                        @endphp
                        <tr data-task-id="{{ $eventId }}"
                            data-task-name="{{ $task->name }}"
                            data-status="{{ $task->status }}"
                            data-duration="{{ $task->duration_in_seconds ?? 0 }}"
                            data-timer-started-at="{{ $task->timer_started_at ? $task->timer_started_at->toIso8601String() : '' }}">
                            <td>{{ $task->name }}</td>
                            <td class="text-muted">{{ $task->start->format('d M Y, h:i A') }}</td>
                            <td>
                                <div class="timer-display-container font-weight-bold"></div>
                            </td>
                            <td>
                                <select class="form-control form-control-sm task-status-select" data-task-id="{{ $eventId }}" data-instance-date="{{ $task->start->toDateString() }}">
                                @foreach($allStatuses as $key => $value)
                                    <option value="{{ $key }}" {{ $task->status == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                                </select>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <div class="timer-actions-container btn-group mr-1"></div>
                                    <div class="btn-group">
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
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Client Tasks Section --}}
    @forelse($clientTaskGroups as $clientName => $services)
        <div class="card mb-2 shadow-sm">
            <div class="card-header p-0" id="heading-client-{{ Str::slug($clientName) }}">
                 <a href="#collapse-client-{{ Str::slug($clientName) }}" class="d-flex justify-content-between align-items-center p-3 text-dark accordion-toggle-link" data-toggle="collapse" aria-expanded="true">
                    <span class="font-weight-bold"><i class="fas fa-building mr-2"></i> Client: {{ $clientName }}</span>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </a>
            </div>
            <div id="collapse-client-{{ Str::slug($clientName) }}" class="collapse show" data-parent="#client-accordion">
                <div class="card-body p-2">
                    @foreach($services as $serviceName => $tasks)
                        <div class="card mb-2">
                            <div class="card-header bg-light p-0" id="heading-service-{{ Str::slug($clientName.$serviceName) }}">
                                <a href="#collapse-service-{{ Str::slug($clientName.$serviceName) }}" class="d-flex justify-content-between align-items-center p-2 text-dark accordion-toggle-link" data-toggle="collapse" aria-expanded="true">
                                    <h6 class="mb-0 font-weight-bold"><i class="fas fa-concierge-bell mr-2"></i> Service: {{ $serviceName }}</h6>
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </a>
                            </div>
                            <div id="collapse-service-{{ Str::slug($clientName.$serviceName) }}" class="collapse show">
                                <div class="card-body p-0">
                                    <table class="table table-hover mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 45%;">Task</th>
                                                <th style="width: 20%;">Due Date</th>
                                                <th style="width: 10%;">Time Logged</th>
                                                <th style="width: 15%;">Status</th>
                                                <th style="width: 10%;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tasks as $task)
                                                @php
                                                    $eventId = 'a_' . $task->id;
                                                    if ($task->is_recurring) {
                                                        $eventId .= '_' . $task->due_date_instance->toDateString();
                                                    }
                                                @endphp
                                                <tr data-task-id="{{ $eventId }}"
                                                    data-task-name="{{ $task->name }}"
                                                    data-status="{{ $task->status }}"
                                                    data-duration="{{ $task->duration_in_seconds ?? 0 }}"
                                                    data-timer-started-at="{{ $task->timer_started_at ? $task->timer_started_at->toIso8601String() : '' }}">
                                                    <td>{{ $task->name }}</td>
                                                    <td class="text-muted">{{ $task->due_date_instance->format('d M Y, h:i A') }}</td>
                                                    <td>
                                                        <div class="timer-display-container font-weight-bold"></div>
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
                                                            <div class="btn-group">
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
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        @if($personalTasks->isEmpty())
            <div class="text-center p-4 text-muted">No client tasks found for the selected criteria.</div>
        @endif
    @endforelse
</div>