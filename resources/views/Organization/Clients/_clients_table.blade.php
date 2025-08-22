<!-- Hidden inputs to store current sort state -->
<input type="hidden" id="sort_by" value="{{ $sort_by }}">
<input type="hidden" id="sort_order" value="{{ $sort_order }}">

<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th style="width: 10px;"><input type="checkbox" id="master-checkbox"></th>
            <th>
                <a href="#" class="sort-link" data-sortby="name" data-sortorder="{{ $sort_by == 'name' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Name
                    @if($sort_by == 'name') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>
            <th>
                <a href="#" class="sort-link" data-sortby="email" data-sortorder="{{ $sort_by == 'email' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Email
                    @if($sort_by == 'email') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>
            <th>Phone</th>
            <th>
                <a href="#" class="sort-link" data-sortby="status" data-sortorder="{{ $sort_by == 'status' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Status
                    @if($sort_by == 'status') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>
            <th>
                <a href="#" class="sort-link" data-sortby="created_at" data-sortorder="{{ $sort_by == 'created_at' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Date Added
                    @if($sort_by == 'created_at') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($clients as $client)
        <tr>
            <td><input type="checkbox" class="client-checkbox" data-id="{{ $client->id }}" data-name="{{ $client->name }}"></td>
            <td>
                {{ $client->name }}
            </td>
            <td>{{ $client->email }}</td>
            <td>{{ $client->phone ?? 'N/A' }}</td>
            <td>
                @if($client->status == 'A')
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>
            <td>{{ $client->created_at->format('d M Y') }}</td>
            <td>
                <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-xs btn-warning">Edit</a>
                
                <form action="{{ route('clients.toggleStatus', $client->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $client->status === 'A' ? 'deactivate' : 'activate' }} this client?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-xs {{ $client->status === 'A' ? 'btn-secondary' : 'btn-info' }}">
                        {{ $client->status === 'A' ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>

                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this client? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-center">No clients found.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="mt-3 d-flex justify-content-center">
    {{ $clients->appends(request()->query())->links() }}
</div>