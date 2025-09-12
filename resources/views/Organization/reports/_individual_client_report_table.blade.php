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

<div class="card card-primary card-outline">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 40%;">Service / Task</th>
                        <th>Assigned Staff</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-right">Time Logged</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedTasks as $serviceName => $data)
                        @php $serviceId = Str::slug($serviceName); @endphp
                        <tr class="service-row" data-service-id="{{ $serviceId }}">
                            <td>
                                <i class="fas fa-chevron-down collapse-icon mr-2"></i>
                                {{ $serviceName }}
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-right font-weight-bold">{{ \App\Helpers\TimeHelper::formatToHms($data['total_duration'], true) }}</td>
                        </tr>
                        @foreach($data['tasks'] as $task)
                        <tr class="task-row task-for-service-{{ $serviceId }}">
                            <td class="task-name-cell">{{ $task->name }}</td>
                            <td>{{ $task->staff->pluck('name')->join(', ') }}</td>
                            <td>{{ $task->due_date->format('d M Y') }}</td>
                            <td>
                                @if($task->status == 'completed') <span class="badge badge-success">Completed</span>
                                @elseif($task->status == 'ongoing') <span class="badge badge-info">Ongoing</span>
                                @else <span class="badge badge-secondary">To Do</span>
                                @endif
                            </td>
                            <td class="text-right">{{ \App\Helpers\TimeHelper::formatToHms($task->duration_in_seconds, true) }}</td>
                        </tr>
                        @endforeach
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted p-4">No tasks found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>