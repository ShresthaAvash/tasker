@php
    if (!function_exists('formatToHms')) {
        function formatToHms($seconds) {
            if ($seconds <= 0) return '00:00:00';
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            $s = $seconds % 60;
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }
    }

    if (!function_exists('getAggregateStatus')) {
        function getAggregateStatus($tasks) {
            if ($tasks->isEmpty()) return 'Not Started Yet';
            $statuses = $tasks->pluck('status')->unique();
            $hasTime = $tasks->sum('duration_in_seconds') > 0;

            if ($statuses->contains('ongoing') || ($hasTime && !$statuses->every(fn($s) => $s === 'completed'))) return 'In Progress';
            if ($statuses->every(fn($s) => $s === 'completed')) return 'Completed';
            return 'Not Started Yet';
        }
    }
@endphp

@forelse($groupedTasks as $clientName => $services)
    @php
        $allClientTasks = $services->flatMap(fn($jobs) => $jobs->flatMap(fn($tasks) => $tasks));
        $clientStatus = getAggregateStatus($allClientTasks);
        $clientStatusClass = 'status-' . Str::slug($clientStatus);
    @endphp

    <div class="client-block">
        <div class="block-header client-header d-flex justify-content-between align-items-center">
            <h5 class="block-title"><i class="fas fa-user-tie"></i> Client: {{ $clientName }}</h5>
            <span class="block-status">{{ $clientStatus }}</span>
        </div>

        @foreach($services as $serviceName => $jobs)
            @php
                $allServiceTasks = $jobs->flatMap(fn($tasks) => $tasks);
                $serviceStatus = getAggregateStatus($allServiceTasks);
            @endphp
            <div class="block-header service-header d-flex justify-content-between align-items-center">
                <h6 class="block-title"><i class="fas fa-concierge-bell"></i> Annual Accounting</h6>
                 <span class="block-status">{{ $serviceStatus }}</span>
            </div>

            @foreach($jobs as $jobName => $tasks)
                 @php
                    $jobStatus = getAggregateStatus($tasks);
                @endphp
                <div class="block-header job-header d-flex justify-content-between align-items-center">
                    <h6 class="block-title"><i class="fas fa-briefcase"></i> {{ $jobName }}</h6>
                    <span class="block-status">{{ $jobStatus }}</span>
                </div>

                <div class="task-list">
                    @foreach($tasks as $task)
                        @php
                            $taskStatusText = match($task->status) {
                                'ongoing' => 'In Progress',
                                'to_do' => 'To Do',
                                default => 'Completed',
                            };
                             $taskStatusClass = 'status-' . Str::slug($taskStatusText);
                        @endphp
                        <div class="task-item">
                            <div class="task-main-row">
                                <div class="task-details">
                                    <i class="far fa-file-alt"></i>
                                    <div>
                                        <div class="task-name">{{ $task->name }}</div>
                                         @if($task->staff->isNotEmpty())
                                            <a href="#staff-breakdown-{{ $task->id }}" data-toggle="collapse" class="task-staff">
                                                Assigned Staff ({{ $task->staff->count() }}) <i class="fas fa-chevron-down fa-xs collapse-icon"></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="task-status">
                                    <span class="status-pill {{ $taskStatusClass }}">{{ $taskStatusText }}</span>
                                </div>
                            </div>
                            @if($task->staff->isNotEmpty())
                            <div class="collapse staff-breakdown mt-2" id="staff-breakdown-{{ $task->id }}">
                                <ul class="list-unstyled p-2 mb-0">
                                    @foreach($task->staff as $staffMember)
                                        <li class="d-flex justify-content-between border-bottom py-1 px-2">
                                            <span class="text-muted">{{ $staffMember->name }}</span>
                                            <span class="text-muted font-weight-bold">{{ formatToHms($staffMember->pivot->duration_in_seconds) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endforeach
    </div>
@empty
    <div class="card">
        <div class="card-body text-center p-5 text-muted">
            <h4>No Tasks Found</h4>
            <p>There are no tasks that match the selected criteria.</p>
        </div>
    </div>
@endforelse