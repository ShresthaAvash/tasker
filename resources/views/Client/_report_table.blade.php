@php
    function formatToHms($seconds) {
        if ($seconds < 0) $seconds = 0;
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
@endphp

<div class="accordion" id="serviceReportAccordion">
    @forelse($groupedTasks as $serviceName => $jobs)
        @php
            $serviceTotalDuration = 0;
            foreach ($jobs as $tasks) {
                $serviceTotalDuration += $tasks->sum('duration_in_seconds');
            }
        @endphp
        <!-- Service Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header p-0 report-header-service" id="heading-service-{{ $loop->index }}">
                <a href="#collapse-service-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3" data-toggle="collapse" aria-expanded="true">
                    <span><i class="fas fa-concierge-bell mr-2"></i> Service: {{ $serviceName }}</span>
                    <span class="total-time-display">{{ formatToHms($serviceTotalDuration) }}</span>
                </a>
            </div>
            <div id="collapse-service-{{ $loop->index }}" class="collapse show" data-parent="#serviceReportAccordion">
                <div class="card-body p-2">
                    @foreach($jobs as $jobName => $tasks)
                        <!-- Job Card -->
                         <div class="card mb-2">
                            <div class="card-header p-0 report-header-job" id="heading-job-{{ $loop->parent->index }}-{{ $loop->index }}">
                                 <a href="#collapse-job-{{ $loop->parent->index }}-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3" data-toggle="collapse" aria-expanded="true">
                                    <span><i class="fas fa-briefcase mr-2"></i> Job: {{ $jobName }}</span>
                                    <span class="total-time-display">{{ formatToHms($tasks->sum('duration_in_seconds')) }}</span>
                                </a>
                            </div>
                            <div id="collapse-job-{{ $loop->parent->index }}-{{ $loop->index }}" class="collapse show">
                                <ul class="list-group list-group-flush">
                                    @foreach($tasks as $task)
                                        <!-- Task Item -->
                                         <li class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>{{ $task->name }}</strong>
                                                    @if($task->staff->isNotEmpty())
                                                    <a href="#staff-breakdown-{{ $task->id }}" data-toggle="collapse" class="d-block text-muted small">
                                                        Assigned Staff ({{ $task->staff->count() }}) <i class="fas fa-chevron-down fa-xs collapse-icon"></i>
                                                    </a>
                                                    @endif
                                                </div>
                                                <span class="font-weight-bold">{{ formatToHms($task->duration_in_seconds) }}</span>
                                            </div>
                                            @if($task->staff->isNotEmpty())
                                            <div class="collapse staff-breakdown mt-2" id="staff-breakdown-{{ $task->id }}">
                                                <ul class="list-unstyled p-2">
                                                    @foreach($task->staff as $staffMember)
                                                        @if($staffMember->pivot->duration_in_seconds > 0)
                                                            <li class="d-flex justify-content-between border-bottom py-1">
                                                                <span class="text-muted">{{ $staffMember->name }}</span>
                                                                <span class="text-muted">{{ formatToHms($staffMember->pivot->duration_in_seconds) }}</span>
                                                            </li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                            @endif
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
        <div class="text-center p-4 text-muted">
            <h4>No Tasks Found</h4>
            <p>There are no ongoing or completed tasks that match the selected criteria.</p>
        </div>
    @endforelse
</div>