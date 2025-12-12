<!-- Hidden inputs to store current sort state -->
<input type="hidden" id="sort_by" value="{{ $sort_by }}">
<input type="hidden" id="sort_order" value="{{ $sort_order }}">

<table class="table table-hover table-striped">
    <thead>
        <tr>
            <th style="width: 10px;"><input type="checkbox" id="master-checkbox"></th>
            
            <!-- 1. Name -->
            <th>
                <a href="#" class="sort-link" data-sortby="name" data-sortorder="{{ $sort_by == 'name' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Name
                    @if($sort_by == 'name') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>

            <!-- 2. Contacts (Merged Email & Phone) -->
            <th>
                <a href="#" class="sort-link" data-sortby="email" data-sortorder="{{ $sort_by == 'email' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Contacts
                    @if($sort_by == 'email') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>

            <!-- 3. NEW: Company Column -->
            <th>Company</th>

            <!-- 3. Status -->
            <th>
                <a href="#" class="sort-link" data-sortby="status" data-sortorder="{{ $sort_by == 'status' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Status
                    @if($sort_by == 'status') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>

            <!-- 4. Date Added -->
            <th>
                <a href="#" class="sort-link" data-sortby="created_at" data-sortorder="{{ $sort_by == 'created_at' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                    Date Added
                    @if($sort_by == 'created_at') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                </a>
            </th>

            <!-- 5. Services (New Column) -->
            <th>Services</th>

            <!-- 6. Actions -->
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($clients as $client)
        <tr>
            <td><input type="checkbox" class="client-checkbox" data-id="{{ $client->id }}" data-name="{{ $client->name }}"></td>
            
            <!-- Name -->
            <td style="vertical-align: middle;">
                <strong>{{ $client->name }}</strong>
            </td>

            <!-- Contacts (Email Top, Phone Bottom) -->
            <td style="vertical-align: middle;">
                <div class="d-flex flex-column">
                    <span><i class="fas fa-envelope text-muted mr-1" style="font-size: 0.8rem;"></i> {{ $client->email }}</span>
                    @if($client->phone)
                        <span class="text-muted small mt-1"><i class="fas fa-phone mr-1" style="font-size: 0.8rem;"></i> {{ $client->phone }}</span>
                    @else
                        <span class="text-muted small mt-1">N/A</span>
                    @endif
                </div>
            </td>

             <!-- NEW: Company Data -->
            <td style="vertical-align: middle;">
                {{ $client->company_name ?? 'N/A' }}
            </td>

            <!-- Status -->
            <td style="vertical-align: middle;">
                @if($client->status == 'A')
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-danger">Inactive</span>
                @endif
            </td>

            <!-- Date Added -->
            <td style="vertical-align: middle;">{{ $client->created_at->format('d M Y') }}</td>

            <!-- Services List -->
            <td style="vertical-align: middle;">
                @if($client->assignedServices->count() > 0)
                    <ul class="list-unstyled mb-0 small">
                        @foreach($client->assignedServices as $index => $service)
                            <li class="text-truncate" style="max-width: 200px;" title="{{ $service->name }}">
                                <strong>{{ $index + 1 }}.</strong> {{ $service->name }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <span class="text-muted small font-italic">No services assigned</span>
                @endif
            </td>

            <!-- Actions (Updated to Text Buttons) -->
            <td style="vertical-align: middle;">
                <a href="{{ route('organization.reports.individual_client', $client->id) }}" class="btn btn-xs btn-info">Report</a>
                <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-xs btn-warning">Edit</a>
                
                <form action="{{ route('clients.toggleStatus', $client->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $client->status === 'A' ? 'deactivate' : 'activate' }} this client?');">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-xs {{ $client->status === 'A' ? 'btn-secondary' : 'btn-success' }}">
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
        <tr><td colspan="6" class="text-center">No clients found.</td></tr>
        @endforelse
    </tbody>
</table>
<div class="mt-3 d-flex justify-content-center">
    {{ $clients->appends(request()->query())->links() }}
</div>