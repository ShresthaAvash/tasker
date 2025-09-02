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

    if (!function_exists('getAggregateStatusForStaff')) {
        function getAggregateStatusForStaff($tasks) {
            if (empty($tasks)) return 'Not Started Yet';
            
            $taskCollection = collect($tasks);
            $statuses = $taskCollection->pluck('status')->unique();
            $hasTime = $taskCollection->sum('duration') > 0;

            if ($statuses->contains('ongoing') || ($hasTime && !$statuses->every(fn($status) => $status === 'completed'))) return 'In Progress';
            if ($statuses->every(fn($status) => $status === 'completed')) return 'Completed';
            return 'Not Started Yet';
        }
    }
@endphp

@forelse($reportData as $staffData)
    @php
        $allStaffTasks = collect($staffData->services)->flatMap(fn($service) => collect($service['jobs'])->flatMap(fn($job) => $job['tasks']));
        $staffStatus = getAggregateStatusForStaff($allStaffTasks);
        $staffStatusClass = 'status-' . Str::slug($staffStatus);
    @endphp

    <div class="staff-block">
        <div class="block-header staff-header d-flex justify-content-between align-items-center">
            <h5 class="block-title"><i class="fas fa-user-circle"></i> Staff: {{ $staffData->staff_name }}</h5>
            <span class="block-status">{{ formatToHms($staffData->total_duration) }}</span>
        </div>

        @foreach($staffData->services as $service)
            @php
                $allServiceTasks = collect($service['jobs'])->flatMap(fn($job) => $job['tasks']);
                $serviceStatus = getAggregateStatusForStaff($allServiceTasks);
            @endphp
            <div class="block-header service-header d-flex justify-content-between align-items-center">
                <h6 class="block-title"><i class="fas fa-concierge-bell"></i> Service: {{ $service['name'] }}</h6>
                <span class="block-status">{{ formatToHms($service['total_duration']) }}</span>
            </div>

            @foreach($service['jobs'] as $job)
                @php
                    $jobStatus = getAggregateStatusForStaff(collect($job['tasks']));
                @endphp
                 <div class="block-header job-header d-flex justify-content-between align-items-center">
                    <h6 class="block-title"><i class="fas fa-briefcase"></i> Job: {{ $job['name'] }}</h6>
                    <span class="block-status">{{ formatToHms($job['total_duration']) }}</span>
                </div>

                <div class="task-list">
                    @foreach($job['tasks'] as $task)
                         @php
                            $taskStatus = $task['status'] ?? 'to_do';
                            $taskStatusClass = 'status-' . Str::slug($taskStatus, '-');
                            $taskStatusText = match($taskStatus) {
                                'ongoing' => 'In Progress',
                                'to_do' => 'To Do',
                                default => 'Completed',
                            };
                        @endphp
                        <div class="task-item">
                            <div class="task-main-row">
                                <div class="task-details">
                                    <i class="far fa-file-alt"></i>
                                    <div>
                                        <div class="task-name">{{ $task['name'] }}</div>
                                        <small class="text-muted">Time: {{ formatToHms($task['duration']) }}</small>
                                    </div>
                                </div>
                                <div class="task-status">
                                   <span class="status-pill {{ $taskStatusClass }}">{{ $taskStatusText }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endforeach
    </div>
@empty
    <div class="card">
        <div class="card-body text-center p-5 text-muted">
            <h4>No Staff Data Found</h4>
            <p>No tasks match the selected criteria.</p>
        </div>
    </div>
@endforelse

<style> 
 .status-in-progress { background-color: #cce5ff; color: #004085; border: 1px solid #b8daff; }

</style>