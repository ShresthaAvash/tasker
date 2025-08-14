<!-- Hidden inputs to store current sort state -->
<input type="hidden" id="sort_by" value="{{ $sort_by }}">
<input type="hidden" id="sort_order" value="{{ $sort_order }}">

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>
                    <a href="#" class="sort-link" data-sortby="name" data-sortorder="{{ $sort_by == 'name' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                        Task Name @if($sort_by == 'name') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                    </a>
                </th>
                <th>
                    <a href="#" class="sort-link" data-sortby="client_name" data-sortorder="{{ $sort_by == 'client_name' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                        Client/Type @if($sort_by == 'client_name') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                    </a>
                </th>
                <th>
                    <a href="#" class="sort-link" data-sortby="due_date" data-sortorder="{{ $sort_by == 'due_date' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                        Due Date @if($sort_by == 'due_date') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                    </a>
                </th>
                <th>
                    <a href="#" class="sort-link" data-sortby="status" data-sortorder="{{ $sort_by == 'status' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                        Status @if($sort_by == 'status') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                    </a>
                </th>
                @if($status === 'pending')
                <th style="width: 150px;">Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($tasks as $task)
            <tr>
                <td>{{ $task->name }}</td>
                <td>
                    @if($task->type === 'personal')
                        <span class="badge badge-info">{{ $task->client_name }}</span>
                    @else
                        {{ $task->client_name }}
                    @endif
                </td>
                <td>{{ $task->due_date ? Carbon\Carbon::parse($task->due_date)->format('d M Y, h:i A') : 'N/A' }}</td>
                <td>
                    @if($task->status === 'Completed')
                        <span class="badge badge-success">{{ $task->status }}</span>
                    @else
                        <span class="badge badge-warning">{{ $task->status }}</span>
                    @endif
                </td>
                @if($status === 'pending')
                <td>
                    <form action="{{ route('staff.tasks.complete', $task->id) }}" method="POST" class="d-inline complete-task-form">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-xs btn-success">
                            <i class="fas fa-check"></i> Mark Complete
                        </button>
                    </form>
                </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="{{ $status === 'pending' ? '5' : '4' }}" class="text-center">No tasks found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $tasks->links() }}
</div>