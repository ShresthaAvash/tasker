@php
    // Helper function to get an aggregate status for a collection of tasks
    if (!function_exists('getAggregateStatus')) {
        function getAggregateStatus($tasks) {
            if ($tasks->isEmpty()) return 'Not Started Yet';
            $statuses = $tasks->pluck('status')->unique();
            
            if ($statuses->contains('ongoing')) return 'In Progress';
            if ($statuses->every(fn($s) => $s === 'completed')) return 'Completed';
            if ($statuses->every(fn($s) => $s === 'to_do')) return 'To do';

            return 'In Progress'; // Default for mixed statuses
        }
    }

    // Helper function to format seconds into H:M:S
    if (!function_exists('formatToHmsOrgClient')) {
        function formatToHmsOrgClient($seconds) {
            if ($seconds <= 0) return '00:00:00';
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            $s = $seconds % 60;
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }
    }
@endphp

<div id="org-client-report-accordion">
    @forelse($groupedTasks as $clientName => $services)
        @php
            $allClientTasks = $services->flatMap(fn($jobs) => $jobs->flatMap(fn($tasks) => $tasks));
            $clientStatus = getAggregateStatus($allClientTasks);
            $clientTotalDuration = $allClientTasks->sum('duration_in_seconds');
        @endphp
        <div class="report-block">
            <a href="#collapse-client-{{ $loop->index }}" class="report-header client-header" data-toggle="collapse" aria-expanded="true">
                <h5 class="report-title mb-0"><i class="fas fa-user-tie"></i> Client: {{ $clientName }}</h5>
                <div class="d-flex align-items-center">
                    {{-- THIS IS THE FIX: Added total time for the client --}}
                    <span class="report-time mr-3"><strong>Total Time:</strong> {{ formatToHmsOrgClient($clientTotalDuration) }}</span>
                    <i class="fas fa-chevron-up collapse-icon"></i>
                </div>
            </a>
            <div id="collapse-client-{{ $loop->index }}" class="collapse show">
                <div class="card-body p-0">
                    @foreach($services as $serviceName => $jobs)
                        @php
                            $allServiceTasks = $jobs->flatMap(fn($tasks) => $tasks);
                            $serviceStatus = getAggregateStatus($allServiceTasks);
                            $serviceTotalDuration = $allServiceTasks->sum('duration_in_seconds');
                        @endphp
                        <a href="#collapse-service-{{ $loop->parent->index }}-{{ $loop->index }}" class="report-header service-header" data-toggle="collapse" aria-expanded="true">
                            <h6 class="report-title mb-0 ml-2"><i class="fas fa-concierge-bell"></i> Service: {{ $serviceName }}</h6>
                            <div class="d-flex align-items-center">
                                {{-- THIS IS THE FIX: Added total time for the service --}}
                                <span class="report-time mr-3"><strong>Total Time:</strong> {{ formatToHmsOrgClient($serviceTotalDuration) }}</span>
                                <i class="fas fa-chevron-up collapse-icon"></i>
                            </div>
                        </a>
                        <div id="collapse-service-{{ $loop->parent->index }}-{{ $loop->index }}" class="collapse show">
                            @foreach($jobs as $jobName => $tasks)
                                @php
                                    $jobStatus = getAggregateStatus($tasks);
                                    $jobTotalDuration = $tasks->sum('duration_in_seconds');
                                @endphp
                                <a href="#collapse-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}" class="report-header job-header" data-toggle="collapse" aria-expanded="true">
                                    <p class="report-title mb-0 ml-4"><i class="fas fa-briefcase"></i> Job: {{ $jobName }}</p>
                                    <div class="d-flex align-items-center">
                                        {{-- THIS IS THE FIX: Added total time for the job --}}
                                        <span class="report-time mr-3"><strong>Total Time:</strong> {{ formatToHmsOrgClient($jobTotalDuration) }}</span>
                                        <i class="fas fa-chevron-up collapse-icon"></i>
                                    </div>
                                </a>
                                <div id="collapse-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}" class="collapse show">
                                    <div class="task-item-container">
                                        @foreach($tasks as $task)
                                            <div class="task-item">
                                                <div class="task-main-info">
                                                    <div class="task-details ml-4">
                                                        <i class="far fa-file-alt task-icon"></i>
                                                        <div class="task-name">{{ $task->name }}</div>
                                                        <div class="task-meta">
                                                            @if($task->staff->isNotEmpty())
                                                            <a href="#staff-breakdown-{{ $task->id }}-{{ $loop->index }}" data-toggle="collapse" aria-expanded="false">
                                                                Assigned Staff ({{ $task->staff->count() }}) <i class="fas fa-chevron-down fa-xs"></i>
                                                            </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="task-status">
                                                        @php
                                                            $statusText = ucfirst(str_replace('_', ' ', $task->status));
                                                            $statusSlug = str_replace(' ', '-', strtolower($statusText));
                                                        @endphp
                                                        <span class="status-pill status-{{ $statusSlug }}">
                                                            {{ $statusText }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @if($task->staff->isNotEmpty())
                                                <div class="collapse staff-breakdown mt-2" id="staff-breakdown-{{ $task->id }}-{{ $loop->index }}">
                                                    <ul class="list-unstyled p-2 mb-0">
                                                        @foreach($task->staff as $staffMember)
                                                            @if($staffMember->pivot->duration_in_seconds > 0)
                                                                <li class="d-flex justify-content-between border-bottom py-1 px-2">
                                                                    <span class="text-muted">{{ $staffMember->name }}</span>
                                                                    <span class="text-muted font-weight-bold">{{ formatToHmsOrgClient($staffMember->pivot->duration_in_seconds) }}</span>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
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
</div>