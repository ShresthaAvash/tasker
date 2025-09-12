@php
    function formatToHms($seconds) {
        if ($seconds < 0) return 'Not Started Yet';
        if ($seconds == 0) return 'Not Started Yet';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
@endphp

<div id="client-report-accordion">
    @forelse($groupedTasks as $serviceName => $tasks)
        @php
            $serviceTotalDuration = $tasks->sum('duration_in_seconds');
        @endphp
        
        <div class="report-group">
            <a href="#collapse-service-{{ Str::slug($serviceName) }}" class="report-header service" data-toggle="collapse" aria-expanded="true">
                <h5 class="report-title mb-0"><i class="fas fa-concierge-bell mr-2"></i> Service: {{ $serviceName }}</h5>
                <div class="d-flex align-items-center">
                    <span class="report-time mr-3">
                        {{ formatToHms($serviceTotalDuration) }}
                    </span>
                    <i class="fas fa-chevron-up collapse-icon"></i>
                </div>
            </a>
            <div id="collapse-service-{{ Str::slug($serviceName) }}" class="collapse show">
                <div class="card-body p-0">
                    @foreach($tasks as $task)
                        @php
                            $statusClass = 'status-' . str_replace(' ', '_', $task->status);
                        @endphp
                        <div class="task-item">
                            <i class="fas fa-file-alt task-icon"></i>
                            <div class="task-details">
                                <div class="task-name">{{ $task->name }}</div>
                                @if($task->staff->isNotEmpty() && $task->duration_in_seconds > 0)
                                    <a href="#staff-breakdown-client-{{ $task->id }}" data-toggle="collapse" class="task-meta">
                                        Assigned Staff ({{ $task->staff->count() }}) <i class="fas fa-chevron-down fa-xs collapse-icon"></i>
                                    </a>
                                    <div class="collapse staff-breakdown mt-2" id="staff-breakdown-client-{{ $task->id }}">
                                        <ul class="list-unstyled p-2">
                                            @foreach($task->staff as $staffMember)
                                                @if($staffMember->pivot->duration_in_seconds > 0)
                                                    <li class="d-flex justify-content-between border-bottom py-1 px-2">
                                                        <span class="text-muted">{{ $staffMember->name }}</span>
                                                        <span class="text-muted">{{ formatToHms($staffMember->pivot->duration_in_seconds) }}</span>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="mr-3">
                                <button type="button" class="btn btn-xs btn-outline-info open-comments-modal" data-task-id="{{ $task->id }}" data-task-name="{{ $task->name }}">
                                    <i class="fas fa-comments"></i> View Comments
                                </button>
                            </div>
                            <div class="task-status {{ $statusClass }}">
                                {{ $task->status === 'ongoing' ? 'Ongoing' : ucfirst(str_replace('_', ' ', $task->status)) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="card report-card">
            <div class="card-body text-center p-5 text-muted">
                <h4>No Tasks Found</h4>
                <p>There are no tasks that match the selected criteria.</p>
            </div>
        </div>
    @endforelse
</div>

<style>
    .status-completed,.status-ongoing,.status-to-do{
        margin-left:2rem !important;
    }
</style>