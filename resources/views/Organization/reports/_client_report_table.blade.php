@if($groupedTasks->isEmpty())
    <div class="text-center text-muted p-5">
        <h4>No Completed Tasks</h4>
        <p>There are no completed tasks with tracked time for the selected criteria.</p>
    </div>
@else
    <div id="accordion-report">
        @foreach($groupedTasks as $clientName => $services)
        <div class="card client-block mb-3">
            <div class="card-header" id="heading-{{ Str::slug($clientName) }}">
                <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapse-{{ Str::slug($clientName) }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                        <span><i class="fas fa-user-tie mr-2"></i> Client: {{ $clientName }}</span>
                        <span class="time-display client-total-time">00:00:00</span>
                    </button>
                </h2>
            </div>
            <div id="collapse-{{ Str::slug($clientName) }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-parent="#accordion-report">
                <div class="card-body client-body">
                    @foreach($services as $serviceName => $jobs)
                    <div class="service-block">
                        <div class="service-header d-flex justify-content-between align-items-center">
                            <span>Service: {{ $serviceName }}</span>
                            <span class="time-display service-total-time">00:00:00</span>
                        </div>
                        <div class="service-body">
                            @foreach($jobs as $jobName => $tasks)
                            <div class="job-block">
                                <a href="#collapse-{{ Str::slug($clientName.$serviceName.$jobName) }}" class="job-header d-flex justify-content-between align-items-center" data-toggle="collapse" aria-expanded="true">
                                    <span><i class="fas fa-chevron-down collapse-icon mr-2"></i> Job: {{ $jobName }}</span>
                                    <span class="time-display job-total-time">00:00:00</span>
                                </a>
                                <div id="collapse-{{ Str::slug($clientName.$serviceName.$jobName) }}" class="collapse show">
                                    <div class="list-group list-group-flush">
                                        @foreach($tasks as $task)
                                        <div class="list-group-item task-list-item" data-task-time="{{ $task->duration_in_seconds }}">
                                            <div class="task-details">
                                                <strong>{{ $task->name }}</strong>
                                                <div class="staff-list mt-2">
                                                    @if($task->staff->isNotEmpty())
                                                        <a href="#staff-time-{{ $task->id }}" data-toggle="collapse" class="text-secondary small"><i class="fas fa-users mr-1"></i> Assigned Staff ({{ $task->staff->count() }}) <i class="fas fa-chevron-down collapse-icon ml-1"></i></a>
                                                        <div class="collapse mt-2 pl-3" id="staff-time-{{ $task->id }}">
                                                            @foreach($task->staff as $staffMember)
                                                                <div><span class="badge badge-light">{{ $staffMember->name }}:</span> <strong>{{ gmdate('H:i:s', $staffMember->pivot->duration_in_seconds) }}</strong></div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <span class="text-muted small">No staff assigned</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="time-display task-total-time">{{ gmdate('H:i:s', $task->duration_in_seconds) }}</div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif