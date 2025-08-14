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
                        <tr>
                            <td>{{ $task->name }}</td>
                            <td class="text-muted">{{ $task->start->format('d M Y, h:i A') }}</td>
                            <td style="width: 170px;">
                                <select class="form-control form-control-sm task-status-select" data-task-id="p_{{ $task->id }}">
                                @foreach($allStatuses as $key => $value)
                                    <option value="{{ $key }}" {{ $task->status == $key ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                                </select>
                            </td>
                            <td style="width: 220px;" class="text-right">
                                <div class="timer-button-group" style="{{ $task->status !== 'ongoing' ? 'display: none;' : '' }}">
                                    <span class="timer-display font-weight-bold mr-2">{{ gmdate('H:i:s', $task->duration_in_seconds) }}</span>
                                    <button class="btn btn-xs btn-success start-timer-btn" data-task-id="p_{{ $task->id }}" style="{{ $task->timer_started_at ? 'display: none;' : '' }}"><i class="fas fa-play"></i></button>
                                    <button class="btn btn-xs btn-danger stop-timer-btn" data-task-id="p_{{ $task->id }}" style="{{ !$task->timer_started_at ? 'display: none;' : '' }}"><i class="fas fa-stop"></i></button>
                                    <div class="btn-group ml-1">
                                        <button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown"><i class="fas fa-plus"></i></button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item manual-time-btn" href="#" data-task-id="p_{{ $task->id }}">Add Manual Time</a>
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
            <div class="card-header" id="heading-client-{{ Str::slug($clientName) }}">
                <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left font-weight-bold" type="button" data-toggle="collapse" data-target="#collapse-client-{{ Str::slug($clientName) }}">
                       <i class="fas fa-building mr-2"></i> Client: {{ $clientName }}
                    </button>
                </h2>
            </div>
            <div id="collapse-client-{{ Str::slug($clientName) }}" class="collapse show" data-parent="#client-accordion">
                <div class="card-body p-2">
                    <div class="accordion" id="service-accordion-{{ Str::slug($clientName) }}">
                        @foreach($services as $serviceName => $jobs)
                            <div class="card mb-2">
                                <div class="card-header bg-light" id="heading-service-{{ Str::slug($clientName.$serviceName) }}">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left text-dark" type="button" data-toggle="collapse" data-target="#collapse-service-{{ Str::slug($clientName.$serviceName) }}">
                                            <i class="fas fa-concierge-bell mr-2"></i> Service: {{ $serviceName }}
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapse-service-{{ Str::slug($clientName.$serviceName) }}" class="collapse show">
                                    <div class="card-body p-2">
                                        <div class="accordion" id="job-accordion-{{ Str::slug($serviceName) }}">
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
                                                                    <tr>
                                                                        <td>{{ $task->name }}</td>
                                                                        <td class="text-muted">{{ $task->due_date_instance->format('d M Y, h:i A') }}</td>
                                                                        <td style="width: 170px;">
                                                                            <select class="form-control form-control-sm task-status-select" data-task-id="a_{{ $task->id }}">
                                                                            @foreach($allStatuses as $key => $value)
                                                                                <option value="{{ $key }}" {{ $task->status == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                                            @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td style="width: 220px;" class="text-right">
                                                                            <div class="timer-button-group" style="{{ $task->status !== 'ongoing' ? 'display: none;' : '' }}">
                                                                                <span class="timer-display font-weight-bold mr-2">{{ gmdate('H:i:s', $task->duration_in_seconds) }}</span>
                                                                                <button class="btn btn-xs btn-success start-timer-btn" data-task-id="a_{{ $task->id }}" style="{{ $task->timer_started_at ? 'display: none;' : '' }}"><i class="fas fa-play"></i></button>
                                                                                <button class="btn btn-xs btn-danger stop-timer-btn" data-task-id="a_{{ $task->id }}" style="{{ !$task->timer_started_at ? 'display: none;' : '' }}"><i class="fas fa-stop"></i></button>
                                                                                <div class="btn-group ml-1">
                                                                                    <button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown"><i class="fas fa-plus"></i></button>
                                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                                        <a class="dropdown-item manual-time-btn" href="#" data-task-id="a_{{ $task->id }}">Add Manual Time</a>
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
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @empty
        @if($personalTasks->isEmpty())
            <div class="text-center p-4 text-muted">No client tasks found for the selected criteria.</div>
        @endif
    @endforelse
</div>