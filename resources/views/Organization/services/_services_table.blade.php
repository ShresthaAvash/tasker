<!-- Hidden inputs to store current sort state for AJAX calls -->
<input type="hidden" id="sort_by" value="{{ $sort_by }}">
<input type="hidden" id="sort_order" value="{{ $sort_order }}">

<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th>
                <a href="#" class="sort-link" data-sortby="name" data-sortorder="{{ $sort_by == 'name' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Name
                    @if($sort_by == 'name') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>
            <th>Jobs</th>
            <th>
                <a href="#" class="sort-link" data-sortby="status" data-sortorder="{{ $sort_by == 'status' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Status
                    @if($sort_by == 'status') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($services as $service)
        <tr>
            <td>
                <a href="{{ route('services.show', $service->id) }}">{{ $service->name }}</a>
                <p class="text-muted small">{{ Str::limit($service->description, 60) }}</p>
            </td>
            
            <td>{{ $service->jobs->count() }}</td>
            
            <td>
                @if($service->status == 'A')
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>
            <td>
                <a href="{{ route('services.show', $service->id) }}" class="btn btn-xs btn-warning">Edit</a>
                
                <form action="{{ route('services.toggleStatus', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-xs {{ $service->status === 'A' ? 'btn-secondary' : 'btn-success' }}">{{ $service->status === 'A' ? 'Deactivate' : 'Activate' }}</button>
                </form>

                <form action="{{ route('services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this service and all its jobs & tasks?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center">No services found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="mt-3 d-flex justify-content-center">
    {{ $services->appends(request()->query())->links() }}
</div>