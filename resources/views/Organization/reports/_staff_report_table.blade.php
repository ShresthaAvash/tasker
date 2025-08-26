@php
    function formatToHms($seconds) {
        if ($seconds < 0) $seconds = 0;
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
@endphp

<div class="accordion" id="staffReportAccordion">
    @forelse($reportData as $staffData)
        @php
            $allStaffTasksAreToDo = collect($staffData->services)->flatMap(fn($service) => collect($service['jobs'])->flatMap(fn($job) => $job['tasks']))->every(fn($task) => $task['status'] === 'to_do' && $task['duration'] == 0);
        @endphp
        <div class="card shadow-sm mb-3">
            <div class="card-header p-0" id="heading-staff-{{ $loop->index }}" style="background-color: #6c757d; color: white;">
                <a href="#collapse-staff-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3 text-white" data-toggle="collapse" aria-expanded="true" style="text-decoration: none;">
                    <span><i class="fas fa-user mr-2"></i> Staff: {{ $staffData->staff_name }}</span>
                    @if($staffData->total_duration == 0 && $allStaffTasksAreToDo)
                        <span class="font-weight-normal text-white-50">Not Started Yet</span>
                    @else
                        <span class="total-time-display">{{ formatToHms($staffData->total_duration) }}</span>
                    @endif
                </a>
            </div>
            <div id="collapse-staff-{{ $loop->index }}" class="collapse show" data-parent="#staffReportAccordion">
                <div class="card-body p-2">
                    @foreach($staffData->services as $service)
                         @php
                            $allServiceTasksAreToDo = collect($service['jobs'])->flatMap(fn($job) => $job['tasks'])->every(fn($task) => $task['status'] === 'to_do' && $task['duration'] == 0);
                        @endphp
                        <div class="card mb-2">
                             <div class="card-header p-0 report-header-service" id="heading-service-{{ $loop->parent->index }}-{{ $loop->index }}">
                                <a href="#collapse-service-{{ $loop->parent->index }}-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3" data-toggle="collapse" aria-expanded="true" style="text-decoration: none;">
                                    <span><i class="fas fa-concierge-bell mr-2"></i> Service: {{ $service['name'] }}</span>
                                    @if($service['total_duration'] == 0 && $allServiceTasksAreToDo)
                                        <span class="font-weight-normal text-white-50">Not Started Yet</span>
                                    @else
                                        <span class="total-time-display">{{ formatToHms($service['total_duration']) }}</span>
                                    @endif
                                </a>
                            </div>
                            <div id="collapse-service-{{ $loop->parent->index }}-{{ $loop->index }}" class="collapse show">
                                <div class="card-body p-2">
                                    @foreach($service['jobs'] as $job)
                                        @php
                                            $allJobTasksAreToDo = collect($job['tasks'])->every(fn($task) => $task['status'] === 'to_do' && $task['duration'] == 0);
                                        @endphp
                                        <div class="card mb-2">
                                             <div class="card-header p-0 report-header-job" id="heading-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}">
                                                <a href="#collapse-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3" data-toggle="collapse" aria-expanded="true" style="text-decoration: none;">
                                                    <span><i class="fas fa-briefcase mr-2"></i> Job: {{ $job['name'] }}</span>
                                                    @if($job['total_duration'] == 0 && $allJobTasksAreToDo)
                                                        <span class="font-weight-normal text-muted">Not Started Yet</span>
                                                    @else
                                                        <span class="total-time-display">{{ formatToHms($job['total_duration']) }}</span>
                                                    @endif
                                                </a>
                                            </div>
                                            <div id="collapse-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}" class="collapse show">
                                                <ul class="list-group list-group-flush">
                                                    @foreach($job['tasks'] as $task)
                                                        @php
                                                            $statusClass = ['to_do' => 'badge-secondary', 'ongoing' => 'badge-warning', 'completed' => 'badge-success'][$task['status']] ?? 'badge-light';
                                                        @endphp
                                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <strong>{{ $task['name'] }}</strong>
                                                                <span class="badge {{ $statusClass }} ml-2">{{ ucfirst(str_replace('_', ' ', $task['status'])) }}</span>
                                                            </div>
                                                            <span class="font-weight-bold">
                                                                @if($task['status'] === 'to_do' && $task['duration'] == 0)
                                                                    <span class="text-muted font-weight-normal">Not Started Yet</span>
                                                                @else
                                                                    {{ formatToHms($task['duration']) }}
                                                                @endif
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="text-center p-4 text-muted">
            <h4>No Staff Time Found</h4>
            <p>No time has been logged by staff members for the selected criteria.</p>
        </div>
    @endforelse
</div>