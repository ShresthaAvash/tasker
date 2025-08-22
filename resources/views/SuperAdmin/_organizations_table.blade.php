<!-- Hidden inputs to store current sort state -->
<input type="hidden" id="sort_by" value="{{ $sort_by }}">
<input type="hidden" id="sort_order" value="{{ $sort_order }}">

<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
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
                <th>Subscription</th>
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
            @forelse ($organizations as $org)
                <tr>
                    <td>{{ $org->name }}</td>
                    <td>{{ $org->email }}</td>
                    <td>
                        @if($org->subscribed('default'))
                            <span class="badge badge-info">Subscribed</span>
                        @else
                            <span class="badge badge-secondary">Not Subscribed</span>
                        @endif
                    </td>
                    <td>
                        @if ($org->status === 'A')
                            <span class="badge badge-success">Active</span>
                        @elseif ($org->status === 'R')
                            <span class="badge badge-warning">Requested</span>
                        @else
                            <span class="badge badge-danger">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('superadmin.organizations.show', $org->id) }}" class="btn btn-info btn-xs">View</a>
                        <a href="{{ route('superadmin.organizations.edit', $org->id) }}" class="btn btn-warning btn-xs">Edit</a>
                        <form method="POST" action="{{ route('superadmin.organizations.destroy', $org->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $org->status === 'A' ? 'deactivate' : 'activate' }} this organization?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-{{ $org->status === 'A' ? 'secondary' : 'success' }} btn-xs">
                                {{ $org->status === 'A' ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">No organizations found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $organizations->appends(request()->query())->links() }}
</div>