@php
    $totalDuration = $groupedTasks->sum('total_duration');
    $totalTasks = $groupedTasks->reduce(fn($carry, $item) => $carry + $item['tasks']->count(), 0);
    $completedTasks = $groupedTasks->reduce(fn($carry, $item) => $carry + $item['tasks']->where('status', 'completed')->count(), 0);
@endphp

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <p class="stat-title">Total Time Logged</p>
            <h3 class="stat-number">{{ \App\Helpers\TimeHelper::formatToHms($totalDuration, true) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <p class="stat-title">Total Tasks in Period</p>
            <h3 class="stat-number">{{ $totalTasks }}</h3>
        </div>
    </div>
    <div class="col-md-4">
         <div class="stat-card">
            <p class="stat-title">Tasks Completed</p>
            <h3 class="stat-number">{{ $completedTasks }}</h3>
        </div>
    </div>
</div>

<div id="client-report-accordion">
    @forelse($groupedTasks as $serviceName => $data)
        @php 
            $service = $data['service'];
            // Find the pivot data for the current client
            $pivot = $service->clients->where('id', $client->id)->first()->pivot ?? null;
            $currentServiceStatus = $pivot ? $pivot->status : 'Not Started';
        @endphp
        <div class="service-block">
            <div class="service-header" data-toggle="collapse" href="#collapse-service-{{ $service->id }}" aria-expanded="true">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chevron-down collapse-icon mr-3"></i>
                        <h5 class="service-title mb-0">{{ $serviceName }}</h5>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="service-time mr-3">Total Time: <strong>{{ \App\Helpers\TimeHelper::formatToHms($data['total_duration'], true) }}</strong></span>
                        <div class="form-group mb-0" style="min-width: 150px;" onclick="event.stopPropagation();">
                             <select class="form-control form-control-sm service-status-select" data-service-id="{{ $service->id }}">
                                <option value="Not Started" {{ $currentServiceStatus == 'Not Started' ? 'selected' : '' }}>Not Started</option>
                                <option value="Ongoing" {{ $currentServiceStatus == 'Ongoing' ? 'selected' : '' }}>Ongoing</option>
                                <option value="Completed" {{ $currentServiceStatus == 'Completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div id="collapse-service-{{ $service->id }}" class="collapse show">
                <div class="table-responsive">
                    <table class="table task-table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Task</th>
                                <th style="width: 25%;">Assigned Staff</th>
                                <th style="width: 15%;">Due Date</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;" class="text-right">Time Logged</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($data['tasks'] as $task)
                            <tr class="task-item">
                                <td class="task-name-cell">{{ $task->name }}</td>
                                <td class="staff-cell">{{ $task->staff->pluck('name')->join(', ') }}</td>
                                <td class="date-cell">{{ $task->due_date->format('d M Y') }}</td>
                                <td>
                                    <span class="status-badge status-{{ str_replace(' ', '_', $task->status) }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </td>
                                <td class="text-right font-weight-bold">{{ \App\Helpers\TimeHelper::formatToHms($task->duration_in_seconds, true) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center p-3 text-muted">No tasks for this service in the selected period.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
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