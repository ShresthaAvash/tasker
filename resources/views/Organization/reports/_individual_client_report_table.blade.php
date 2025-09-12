@php
    $totalDuration = $taskInstances->sum('duration_in_seconds');
@endphp

<!-- Hidden inputs for sorting state -->
<input type="hidden" id="sort_by" value="{{ $sort_by }}">
<input type="hidden" id="sort_order" value="{{ $sort_order }}">

<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <p class="stat-title">Total Time Logged on Tasks</p>
            <h3 class="stat-number">{{ \App\Helpers\TimeHelper::formatToHms($totalDuration, true) }}</h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <p class="stat-title">Total Tasks in Period</p>
            <h3 class="stat-number">{{ $taskInstances->total() }}</h3>
        </div>
    </div>
    <div class="col-md-4">
         <div class="stat-card">
            <p class="stat-title">Tasks Completed</p>
            <h3 class="stat-number">{{ $taskInstances->where('status', 'completed')->count() }}</h3>
        </div>
    </div>
</div>

<div class="card card-primary card-outline">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th><a href="#" class="sort-link" data-sortby="due_date" data-sortorder="{{ $sort_by == 'due_date' && $sort_order == 'asc' ? 'desc' : 'asc' }}">Due Date @if($sort_by == 'due_date')<i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i>@endif</a></th>
                        <th><a href="#" class="sort-link" data-sortby="name" data-sortorder="{{ $sort_by == 'name' && $sort_order == 'asc' ? 'desc' : 'asc' }}">Task @if($sort_by == 'name')<i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i>@endif</a></th>
                        <th>Service</th>
                        <th>Assigned Staff</th>
                        <th>Status</th>
                        <th class="text-right"><a href="#" class="sort-link" data-sortby="duration_in_seconds" data-sortorder="{{ $sort_by == 'duration_in_seconds' && $sort_order == 'asc' ? 'desc' : 'asc' }}">Time Logged @if($sort_by == 'duration_in_seconds')<i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i>@endif</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taskInstances as $task)
                    <tr>
                        <td>{{ $task->due_date->format('d M Y') }}</td>
                        <td>{{ $task->name }}</td>
                        <td>{{ optional($task->service)->name }}</td>
                        <td>{{ $task->staff->pluck('name')->join(', ') }}</td>
                        <td>
                             @if($task->status == 'completed') <span class="badge badge-success">Completed</span>
                             @elseif($task->status == 'ongoing') <span class="badge badge-info">Ongoing</span>
                             @else <span class="badge badge-secondary">To Do</span>
                             @endif
                        </td>
                        <td class="text-right font-weight-bold">{{ \App\Helpers\TimeHelper::formatToHms($task->duration_in_seconds, true) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">No tasks found for the selected criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($taskInstances->hasPages())
    <div class="card-footer">
        {{ $taskInstances->links() }}
    </div>
    @endif
</div>