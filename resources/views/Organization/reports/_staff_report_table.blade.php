<div class="accordion" id="staffReportAccordion">
    @php
    function formatToHms($seconds) {
        if ($seconds < 0) $seconds = 0;
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
    @endphp
    @forelse($reportData as $staffData)
        <!-- Staff Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header p-0 report-header-staff" id="heading-staff-{{ $loop->index }}">
                <a href="#collapse-staff-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3 font-weight-bold" data-toggle="collapse" aria-expanded="true">
                    <span><i class="fas fa-user-circle mr-2"></i> Staff: {{ $staffData->staff_name }}</span>
                    <span class="total-time-display">{{ formatToHms($staffData->total_duration) }}</span>
                </a>
            </div>

            <div id="collapse-staff-{{ $loop->index }}" class="collapse show" data-parent="#staffReportAccordion">
                <div class="card-body p-2">
                    @forelse($staffData->services as $service)
                        <!-- Service Card -->
                        <div class="card shadow-sm mb-2">
                             <div class="card-header p-0 report-header-service" id="heading-service-{{ $loop->parent->index }}-{{ $loop->index }}">
                                <a href="#collapse-service-{{ $loop->parent->index }}-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3" data-toggle="collapse" aria-expanded="true">
                                    <span><i class="fas fa-concierge-bell mr-2"></i> Service: {{ $service['name'] }}</span>
                                    <span class="total-time-display">{{ formatToHms($service['total_duration']) }}</span>
                                </a>
                            </div>
                            <div id="collapse-service-{{ $loop->parent->index }}-{{ $loop->index }}" class="collapse show">
                                <div class="card-body p-2">
                                    @foreach($service['jobs'] as $job)
                                        <!-- Job Card -->
                                         <div class="card mb-2">
                                            <div class="card-header p-0 report-header-job" id="heading-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}">
                                                 <a href="#collapse-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3" data-toggle="collapse" aria-expanded="true">
                                                    <span><i class="fas fa-briefcase mr-2"></i> Job: {{ $job['name'] }}</span>
                                                    <span class="total-time-display">{{ formatToHms($job['total_duration']) }}</span>
                                                </a>
                                            </div>
                                            <div id="collapse-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}" class="collapse show">
                                                <ul class="list-group list-group-flush">
                                                    @foreach($job['tasks'] as $task)
                                                        <!-- Task Item -->
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <span>{{ $task['name'] }}</span>
                                                            <span class="font-weight-bold">{{ formatToHms($task['duration']) }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                         <p class="text-center p-3 text-muted">No services found for this staff member.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @empty
        <div class="text-center p-4 text-muted">
             <h4>No Data Found</h4>
            <p>No staff members with logged time match the selected criteria.</p>
        </div>
    @endforelse
</div>