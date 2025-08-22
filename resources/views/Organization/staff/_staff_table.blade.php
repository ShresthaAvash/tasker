<!-- Hidden inputs to store current sort state -->
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
            <th>
                <a href="#" class="sort-link" data-sortby="email" data-sortorder="{{ $sort_by == 'email' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Email
                    @if($sort_by == 'email') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>
            <th>Designation</th>
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
        @forelse($staff as $member)
        <tr>
            <td>{{ $member->name }}</td>
            <td>{{ $member->email }}</td>
            <td>{{ $member->designation->name ?? 'N/A' }}</td>
            <td>
                @if($member->status == 'A')
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>
            <td>
                <a href="{{ route('staff.edit', $member->id) }}" class="btn btn-xs btn-warning">Edit</a>
                
                <form action="{{ route('staff.toggleStatus', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $member->status === 'A' ? 'deactivate' : 'activate' }} this staff member?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-xs {{ $member->status === 'A' ? 'btn-secondary' : 'btn-success' }}">
                        {{ $member->status === 'A' ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>

                <form action="{{ route('staff.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-xs btn-danger">Delete</button>
                </form>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center">No staff members found.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="mt-3 d-flex justify-content-center">
    {{ $staff->appends(request()->query())->links() }}
</div>