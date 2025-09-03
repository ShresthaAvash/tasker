@php
    if (!function_exists('formatToHmsOrgStaff')) {
        function formatToHmsOrgStaff($seconds) {
            if ($seconds <= 0) return '00:00:00';
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            $s = $seconds % 60;
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }
    }
@endphp

<div id="org-staff-report-accordion">
@forelse ($reportData as $staffData)
    <div class="report-group">
        <a href="#collapse-staff-{{ $loop->index }}" class="report-header staff-header" data-toggle="collapse" aria-expanded="true">
            <h5 class="report-title mb-0"><i class="fas fa-user-circle mr-2"></i> Staff: {{ $staffData->staff_name }}</h5>
            <div class="d-flex align-items-center">
                <span class="report-time mr-3">Total Time: {{ formatToHmsOrgStaff($staffData->total_duration) }}</span>
                <i class="fas fa-chevron-down collapse-icon"></i>
            </div>
        </a>
        <div id="collapse-staff-{{ $loop->index }}" class="collapse show">
            <div class="card-body p-0">
                @foreach ($staffData->services as $service)
                    <a href="#collapse-staff-{{ $loop->parent->index }}-service-{{ $loop->index }}" class="report-header service-header" data-toggle="collapse" aria-expanded="true">
                        <h6 class="report-title mb-0 ml-4"><i class="fas fa-concierge-bell mr-2"></i> Service: {{ $service['name'] }}</h6>
                        <div class="d-flex align-items-center">
                             <span class="report-time mr-3">Total Time: {{ formatToHmsOrgStaff($service['total_duration']) }}</span>
                            <i class="fas fa-chevron-down collapse-icon"></i>
                        </div>
                    </a>
                    <div id="collapse-staff-{{ $loop->parent->index }}-service-{{ $loop->index }}" class="collapse show">
                        @foreach ($service['jobs'] as $job)
                            <a href="#collapse-staff-{{ $loop->parent->parent->index }}-service-{{ $loop->parent->index }}-job-{{ $loop->index }}" class="report-header job-header" data-toggle="collapse" aria-expanded="true">
                                <p class="report-title mb-0 ml-5"><i class="fas fa-briefcase mr-2"></i> Job: {{ $job['name'] }}</p>
                                <div class="d-flex align-items-center">
                                    <span class="report-time mr-3">Total Time: {{ formatToHmsOrgStaff($job['total_duration']) }}</span>
                                    <i class="fas fa-chevron-down collapse-icon"></i>
                                </div>
                            </a>
                            <div id="collapse-staff-{{ $loop->parent->parent->index }}-service-{{ $loop->parent->index }}-job-{{ $loop->index }}" class="collapse show">
                                <div class="pl-5">
                                    @foreach ($job['tasks'] as $task)
                                        @php
                                            $taskStatus = $task['status'] ?? 'to_do';
                                            $taskStatusClass = 'status-' . Str::slug($taskStatus, '-');
                                            $taskStatusText = match($taskStatus) {
                                                'ongoing' => 'Ongoing',
                                                'to_do' => 'To Do',
                                                default => 'Completed',
                                            };
                                        @endphp
                                        <div class="task-item">
                                            <div class="task-details ml-4">
                                                <i class="far fa-file-alt"></i>
                                                <div>
                                                    <div class="task-name">{{ $task['name'] }}</div>
                                                    <small class="text-muted">Time: {{ formatToHmsOrgStaff($task['duration']) }}</small>
                                                </div>
                                            </div>
                                            <div class="task-status">
                                               <span class="status-pill {{ $taskStatusClass }}">{{ $taskStatusText }}</span>
                                            </div>
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
            <h4>No Staff Data Found</h4>
            <p>No tasks match the selected criteria.</p>
        </div>
    </div>
@endforelse
</div>