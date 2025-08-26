@php
    function formatToHms($seconds) {
        if ($seconds < 0) $seconds = 0;
        $h = floor($seconds / 3600);
        $m = floor(($seconds % 3600) / 60);
        $s = $seconds % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
@endphp

<div class="accordion" id="clientReportAccordion">
    @forelse($groupedTasks as $clientName => $services)
        @php
            $clientTotalDuration = 0;
            $allClientTasksAreToDo = true;
            foreach ($services as $jobs) {
                foreach ($jobs as $tasks) {
                    $clientTotalDuration += $tasks->sum('duration_in_seconds');
                    if ($tasks->contains(fn($task) => $task->status !== 'to_do')) {
                        $allClientTasksAreToDo = false;
                    }
                }
            }
        @endphp
        <!-- Client Card -->
        <div class="card shadow-sm mb-3">
            <div class="card-header p-0" id="heading-client-{{ $loop->index }}" style="background-color: #6c757d; color: white;">
                <a href="#collapse-client-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3 text-white" data-toggle="collapse" aria-expanded="true" style="text-decoration: none;">
                    <span><i class="fas fa-user-tie mr-2"></i> Client: {{ $clientName }}</span>
                    @if($clientTotalDuration == 0 && $allClientTasksAreToDo)
                        <span class="font-weight-normal text-white-50">Not Started Yet</span>
                    @else
                        <span class="total-time-display">{{ formatToHms($clientTotalDuration) }}</span>
                    @endif
                </a>
            </div>
            <div id="collapse-client-{{ $loop->index }}" class="collapse show" data-parent="#clientReportAccordion">
                <div class="card-body p-2">
                    @foreach($services as $serviceName => $jobs)
                        @php
                            $serviceTotalDuration = 0;
                            $allServiceTasksAreToDo = true;
                            foreach ($jobs as $tasks) {
                                $serviceTotalDuration += $tasks->sum('duration_in_seconds');
                                if ($tasks->contains(fn($task) => $task->status !== 'to_do')) {
                                    $allServiceTasksAreToDo = false;
                                }
                            }
                        @endphp
                        <!-- Service Card -->
                        <div class="card mb-2">
                            <div class="card-header p-0 report-header-service" id="heading-service-{{ $loop->parent->index }}-{{ $loop->index }}">
                                <a href="#collapse-service-{{ $loop->parent->index }}-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3" data-toggle="collapse" aria-expanded="true" style="text-decoration: none;">
                                    <span><i class="fas fa-concierge-bell mr-2"></i> Service: {{ $serviceName }}</span>
                                    @if($serviceTotalDuration == 0 && $allServiceTasksAreToDo)
                                        <span class="font-weight-normal text-white-50">Not Started Yet</span>
                                    @else
                                        <span class="total-time-display">{{ formatToHms($serviceTotalDuration) }}</span>
                                    @endif
                                </a>
                            </div>
                            <div id="collapse-service-{{ $loop->parent->index }}-{{ $loop->index }}" class="collapse show">
                                <div class="card-body p-2">
                                    @foreach($jobs as $jobName => $tasks)
                                        @php
                                            $jobTotalDuration = $tasks->sum('duration_in_seconds');
                                            $allJobTasksAreToDo = !$tasks->contains(fn($task) => $task->status !== 'to_do');
                                        @endphp
                                        <!-- Job Card -->
                                        <div class="card mb-2">
                                            <div class="card-header p-0 report-header-job" id="heading-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}">
                                                <a href="#collapse-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}" class="d-flex justify-content-between align-items-center p-3" data-toggle="collapse" aria-expanded="true" style="text-decoration: none;">
                                                    <span><i class="fas fa-briefcase mr-2"></i> Job: {{ $jobName }}</span>
                                                    @if($jobTotalDuration == 0 && $allJobTasksAreToDo)
                                                        <span class="font-weight-normal text-muted">Not Started Yet</span>
                                                    @else
                                                        <span class="total-time-display">{{ formatToHms($jobTotalDuration) }}</span>
                                                    @endif
                                                </a>
                                            </div>
                                            <div id="collapse-job-{{ $loop->parent->parent->index }}-{{ $loop->parent->index }}-{{ $loop->index }}" class="collapse show">
                                                <ul class="list-group list-group-flush">
                                                    @foreach($tasks as $task)
                                                        @php
                                                            $statusClass = ['to_do' => 'badge-secondary', 'ongoing' => 'badge-warning', 'completed' => 'badge-success'][$task->status] ?? 'badge-light';
                                                        @endphp
                                                        <!-- Task Item -->
                                                        <li class="list-group-item">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong>{{ $task->name }}</strong>
                                                                    <span class="badge {{ $statusClass }} ml-2">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                                                    @if($task->staff->isNotEmpty())
                                                                        <a href="#staff-breakdown-org-{{ $task->id }}" data-toggle="collapse" class="d-block text-muted small">
                                                                            Assigned Staff ({{ $task->staff->count() }}) <i class="fas fa-chevron-down fa-xs collapse-icon"></i>
                                                                        </a>
                                                                    @endif
                                                                </div>
                                                                <span class="font-weight-bold">
                                                                    @if($task->status === 'to_do' && $task->duration_in_seconds == 0)
                                                                        <span class="text-muted font-weight-normal">Not Started Yet</span>
                                                                    @else
                                                                        {{ formatToHms($task->duration_in_seconds) }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            @if($task->staff->isNotEmpty())
                                                                <div class="collapse staff-breakdown mt-2" id="staff-breakdown-org-{{ $task->id }}">
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
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="text-center p-4 text-muted">
            <h4>No Tasks Found</h4>
            <p>There are no tasks that match the selected criteria.</p>
        </div>
    @endforelse
</div>