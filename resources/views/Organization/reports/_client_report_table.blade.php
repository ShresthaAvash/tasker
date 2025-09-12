@php
    function formatToHms($seconds) {
        if ($seconds <= 0) return '00:00:00';
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
@endphp

<div id="client-report-accordion">
    @forelse($groupedTasks as $clientName => $services)
        @php
            $clientTotalDuration = 0;
            foreach ($services as $tasks) {
                $clientTotalDuration += $tasks->sum('duration_in_seconds');
            }
        @endphp
        
        <div class="report-group">
            <a href="#collapse-client-{{ Str::slug($clientName) }}" class="report-header client" data-toggle="collapse" aria-expanded="true">
                <h5 class="report-title mb-0"><i class="fas fa-user-tie mr-2"></i> Client: {{ $clientName }}</h5>
                <div class="d-flex align-items-center">
                    <span class="report-time mr-3">
                        Total Time: {{ formatToHms($clientTotalDuration) }}
                    </span>
                    <i class="fas fa-chevron-up collapse-icon"></i>
                </div>
            </a>
            <div id="collapse-client-{{ Str::slug($clientName) }}" class="collapse show">
                <div class="card-body p-0">
                    @foreach($services as $serviceName => $tasks)
                        <a href="#collapse-service-{{ Str::slug($clientName.$serviceName) }}" class="report-header service" data-toggle="collapse" aria-expanded="true">
                            <h6 class="report-title mb-0 ml-4"><i class="fas fa-concierge-bell mr-2"></i> Service: {{ $serviceName }}</h6>
                             <div class="d-flex align-items-center">
                                <span class="report-time mr-3">
                                    {{ formatToHms($tasks->sum('duration_in_seconds')) }}
                                </span>
                                <i class="fas fa-chevron-up collapse-icon"></i>
                            </div>
                        </a>
                        <div id="collapse-service-{{ Str::slug($clientName.$serviceName) }}" class="collapse show">
                            <div class="pl-5">
                                @foreach($tasks as $task)
                                    @php
                                        $statusClass = 'status-' . str_replace(' ', '_', $task->status);
                                    @endphp
                                    <div class="task-item">
                                        <i class="fas fa-file-alt task-icon"></i>
                                        <div class="task-details">
                                            <div class="task-name">{{ $task->name }}</div>
                                            @if($task->staff->isNotEmpty() && $task->duration_in_seconds > 0)
                                                <a href="#staff-breakdown-{{ $task->id }}" data-toggle="collapse" class="task-meta">
                                                    Assigned Staff ({{ $task->staff->count() }}) <i class="fas fa-chevron-down fa-xs collapse-icon"></i>
                                                </a>
                                                <div class="collapse staff-breakdown mt-2" id="staff-breakdown-{{ $task->id }}">
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
                                        <div class="task-status {{ $statusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                        </div>
                                    </div>
                                @endforeach
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