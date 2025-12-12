<!-- Hidden inputs to store current sort state for AJAX calls -->
<input type="hidden" id="sort_by" value="{{ $sort_by }}">
<input type="hidden" id="sort_order" value="{{ $sort_order }}">

<table class="table table-hover table-striped">
    <thead>
        <tr>
            <!-- 1. Name -->
            <th>
                <a href="#" class="sort-link" data-sortby="name" data-sortorder="{{ $sort_by == 'name' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Name
                    @if($sort_by == 'name') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>
            
            <!-- 2. Tasks Count -->
            <th>Tasks</th>

            <!-- 3. Clients (New Column) -->
            <th>Clients</th>

            <!-- 4. Status -->
            <th>
                <a href="#" class="sort-link" data-sortby="status" data-sortorder="{{ $sort_by == 'status' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Status
                    @if($sort_by == 'status') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>
            
            <!-- 5. Actions -->
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($services as $service)
        <tr>
            <!-- Name -->
            <td style="vertical-align: middle;">
                <a href="{{ route('services.show', $service->id) }}"><strong>{{ $service->name }}</strong></a>
                @if($service->description)
                    <p class="text-muted small mb-0">{{ Str::limit($service->description, 60) }}</p>
                @endif
            </td>
            
            <!-- Tasks Count -->
            <td style="vertical-align: middle;">
                <span class="badge badge-light border">{{ $service->tasks->count() }} Tasks</span>
            </td>
            
            <!-- Clients List (Numbered) -->
            <td style="vertical-align: middle;">
                @if($service->clients->count() > 0)
                    <ul class="list-unstyled mb-0 small">
                        @foreach($service->clients as $index => $client)
                            <li class="text-truncate" style="max-width: 200px;" title="{{ $client->name }}">
                                <strong>{{ $index + 1 }}.</strong> {{ $client->name }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <span class="text-muted small font-italic">No clients assigned</span>
                @endif
            </td>

            <!-- Status -->
            <td style="vertical-align: middle;">
                @if($service->status == 'A')
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>

            <!-- Actions -->
            <td style="vertical-align: middle;">
                <div class="btn-group">
                    <a href="{{ route('services.show', $service->id) }}" class="btn btn-xs btn-warning">Edit</a>
                    
                    <form action="{{ route('services.toggleStatus', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-xs {{ $service->status === 'A' ? 'btn-secondary' : 'btn-success' }}">
                            {{ $service->status === 'A' ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>

                    <form action="{{ route('services.destroy', $service->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this service and all its tasks?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center">No services found.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="mt-3 d-flex justify-content-center">
    {{ $services->appends(request()->query())->links() }}
</div>