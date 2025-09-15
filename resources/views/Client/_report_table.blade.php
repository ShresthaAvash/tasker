@forelse($groupedTasks as $serviceName => $data)
    @php
        $service = $data['service'];
        $tasks = $data['tasks'];
        $serviceTotalDuration = $tasks->sum('duration_in_seconds');
        
        // Get the pivot status for the current client
        $client = Auth::user();
        $pivot = optional($service->clients->where('id', $client->id)->first())->pivot;
        $currentServiceStatus = $pivot ? $pivot->status : 'Not Started';

        $statusClass = 'badge-secondary'; // Default for Not Started
        if ($currentServiceStatus === 'Ongoing') { $statusClass = 'badge-info'; }
        if ($currentServiceStatus === 'Completed') { $statusClass = 'badge-success'; }
    @endphp
    
    <div class="report-group">
        <a href="#collapse-service-{{ $service->id }}" class="report-header" data-toggle="collapse" aria-expanded="true">
            <h5 class="report-title mb-0">
                <i class="fas fa-concierge-bell mr-2"></i>
                Service: {{ $serviceName }}
                <span class="badge {{ $statusClass }} ml-2">{{ $currentServiceStatus }}</span>
            </h5>
            <div class="d-flex align-items-center">
                <span class="report-time mr-3">
                    Total Time: {{ \App\Helpers\TimeHelper::formatToHms($serviceTotalDuration, false) }}
                </span>
                <i class="fas fa-chevron-up collapse-icon"></i>
            </div>
        </a>
        <div id="collapse-service-{{ $service->id }}" class="collapse show">
            <ul class="task-list">
                @forelse($tasks as $task)
                    <li class="task-item">
                        <i class="far fa-file-alt task-icon"></i>
                        <div class="task-details">
                            <div class="task-name">{{ $task->name }}</div>
                            @if($task->staff->isNotEmpty())
                                <div class="task-meta">
                                    <a href="#staff-breakdown-client-{{ $task->id }}" data-toggle="collapse">
                                        Assigned Staff ({{ $task->staff->count() }}) <i class="fas fa-chevron-down fa-xs"></i>
                                    </a>
                                </div>
                                <div class="collapse staff-breakdown mt-2" id="staff-breakdown-client-{{ $task->id }}">
                                    <ul class="list-unstyled p-2">
                                        @foreach($task->staff as $staffMember)
                                            <li class="d-flex justify-content-between border-bottom py-1 px-2">
                                                <span class="text-muted">{{ $staffMember->name }}</span>
                                                @if($staffMember->pivot->duration_in_seconds > 0)
                                                    <span class="text-muted">{{ \App\Helpers\TimeHelper::formatToHms($staffMember->pivot->duration_in_seconds, false) }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-info open-comments-modal mr-3" data-task-id="{{ $task->id }}" data-task-name="{{ $task->name }}">
                            <i class="far fa-comment"></i> View Comments
                        </button>
                        <div class="status-pill status-{{ str_replace(' ', '_', $task->status) }}">
                            {{ $task->status === 'ongoing' ? 'Ongoing' : ucfirst(str_replace('_', ' ', $task->status)) }}
                        </div>
                    </li>
                @empty
                    <li class="list-group-item text-center text-muted p-3">No tasks found for this service in the selected period.</li>
                @endforelse
            </ul>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center p-5 text-muted">
            <h4>No Tasks Found</h4>
            <p>There are no tasks that match the selected criteria.</p>
        </div>
    </div>
@endforelse