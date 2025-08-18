<div class="accordion" id="client-accordion">
    {{-- Personal Tasks Section --}}
    @if($personalTasks->isNotEmpty())
    <div class="card mb-2 shadow-sm">
        <div class="card-header bg-info" id="heading-personal">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left text-white font-weight-bold" type="button" data-toggle="collapse" data-target="#collapse-personal">
                    <i class="fas fa-user mr-2"></i> Personal Tasks
                </button>
            </h2>
        </div>
        <div id="collapse-personal" class="collapse show">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <tbody>
                    @foreach($personalTasks as $task)
                        <tr data-task-id="p_{{ $task->id }}"
                            data-task-name="{{ $task->name }}"
                            data-status="{{ $task->status }}"
                            data-duration="{{ $task->duration_in_seconds ?? 0 }}"
                            data-timer-started-at="{{ $task->timer_started_at ? $task->timer_started_at->toIso8601String() : '' }}">
                            <td style="width: 40%;">{{ $task->name }}</td>
                            <td class="text-muted" style="width: 25%;">{{ $task->start->format('d M Y, h:i A') }}</td>
                             <td style="width: 20%;">
                                <div class="timer-controls d-flex align-items-center justify-content-between"></div>
                            </td>
                            <td style="width: 15%;">
                                <select class="form-control form-control-sm task-status-select" data-task-id="p_{{ $task->id }}" data-instance-date="{{ $task->start->toDateString() }}">
                                @foreach($allStatuses as $key => $value)
                                    <option value="{{ $key }}" {{ $task->status == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                                </select>
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
            <div class="card-header" id="heading-client-{{ Str::slug($clientName) }}">
                <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left font-weight-bold" type="button" data-toggle="collapse" data-target="#collapse-client-{{ Str::slug($clientName) }}">
                       <i class="fas fa-building mr-2"></i> Client: {{ $clientName }}
                    </button>
                </h2>
            </div>
            <div id="collapse-client-{{ Str::slug($clientName) }}" class="collapse show" data-parent="#client-accordion">
                <div class="card-body p-2">
                    @foreach($services as $serviceName => $jobs)
                        @foreach($jobs as $jobName => $tasks)
                            <div class="card mb-2">
                                <div class="card-header" id="heading-job-{{ Str::slug($clientName.$jobName) }}">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-job-{{ Str::slug($clientName.$jobName) }}">
                                           <i class="fas fa-briefcase mr-2"></i> Job: {{ $jobName }}
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapse-job-{{ Str::slug($clientName.$jobName) }}" class="collapse show">
                                    <div class="card-body p-0">
                                        <table class="table table-hover mb-0">
                                            <tbody>
                                                @foreach($tasks as $task)
                                                <tr data-task-id="a_{{ $task->id }}"
                                                    data-task-name="{{ $task->name }}"
                                                    data-status="{{ $task->status }}"
                                                    data-duration="{{ $task->duration_in_seconds ?? 0 }}"
                                                    data-timer-started-at="{{ $task->timer_started_at ? $task->timer_started_at->toIso8601String() : '' }}">
                                                    <td style="width: 40%;">{{ $task->name }}</td>
                                                    <td class="text-muted" style="width: 25%;">{{ $task->due_date_instance->format('d M Y, h:i A') }}</td>
                                                    <td style="width: 20%;">
                                                        <div class="timer-controls d-flex align-items-center justify-content-between"></div>
                                                    </td>
                                                    <td style="width: 15%;">
                                                        <select class="form-control form-control-sm task-status-select" data-task-id="a_{{ $task->id }}" data-instance-date="{{ $task->due_date_instance->toDateString() }}">
                                                        @foreach($allStatuses as $key => $value)
                                                            <option value="{{ $key }}" {{ $task->status == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                        @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
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