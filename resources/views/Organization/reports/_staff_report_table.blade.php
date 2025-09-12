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

<div id="staff-report-accordion">
    @forelse($reportData as $staffData)
        <div class="report-group">
            <a href="#collapse-staff-{{ $loop->index }}" class="report-header client" data-toggle="collapse" aria-expanded="true">
                <h5 class="report-title mb-0"><i class="fas fa-user-tie mr-2"></i> Staff: {{ $staffData->staff_name }}</h5>
                <div class="d-flex align-items-center">
                    <span class="report-time mr-3">Total Time: {{ formatToHmsOrgStaff($staffData->total_duration) }}</span>
                    <i class="fas fa-chevron-down collapse-icon"></i>
                </div>
            </a>
            <div id="collapse-staff-{{ $loop->index }}" class="collapse show">
                <div class="card-body p-0">
                    @foreach ($staffData->services as $service)
                        <a href="#collapse-staff-{{ $loop->parent->index }}-service-{{ $loop->index }}" class="report-header service" data-toggle="collapse" aria-expanded="true">
                            <h6 class="report-title mb-0 ml-4"><i class="fas fa-concierge-bell mr-2"></i> Service: {{ $service['name'] }}</h6>
                            <div class="d-flex align-items-center">
                                <span class="report-time mr-3">Total Time: {{ formatToHmsOrgStaff($service['total_duration']) }}</span>
                                <i class="fas fa-chevron-down collapse-icon"></i>
                            </div>
                        </a>
                        <div id="collapse-staff-{{ $loop->parent->index }}-service-{{ $loop->index }}" class="collapse show">
                            <div class="pl-5">
                                @foreach($service['tasks'] as $task)
                                    @php
                                        $statusClass = 'status-' . str_replace(' ', '_', $task['status']);
                                    @endphp
                                    <div class="task-item">
                                        <i class="fas fa-file-alt task-icon"></i>
                                        <div class="task-details">
                                            <div class="task-name">{{ $task['name'] }}</div>
                                        </div>
                                        <div class="task-time font-weight-bold">
                                            {{ formatToHmsOrgStaff($task['duration']) }}
                                        </div>
                                        <div class="task-status {{ $statusClass }}">
                                            {{ ucfirst(str_replace('_', ' ', $task['status'])) }}
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
                <h4>No Staff Time Logged</h4>
                <p>There is no time-tracking data for staff members matching the selected criteria.</p>
            </div>
        </div>
    @endforelse
</div>