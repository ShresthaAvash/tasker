<!-- Hidden inputs to store current sort state for AJAX calls -->
<input type="hidden" id="sort_by" value="{{ $sort_by }}">
<input type="hidden" id="sort_order" value="{{ $sort_order }}">

<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th>
                    <a href="#" class="sort-link" data-sortby="name" data-sortorder="{{ $sort_by == 'name' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                        Organization
                        @if($sort_by == 'name') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                    </a>
                </th>
                <th>Plan</th>
                <th>Status</th>
                <th>
                    <a href="#" class="sort-link" data-sortby="created_at" data-sortorder="{{ $sort_by == 'created_at' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                        Date Added
                        @if($sort_by == 'created_at') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                    </a>
                </th>
                <th>
                    <a href="#" class="sort-link" data-sortby="ends_at" data-sortorder="{{ $sort_by == 'ends_at' && $sort_order == 'asc' ? 'desc' : 'asc' }}">
                        Renews / Ends On
                        @if($sort_by == 'ends_at') <i class="fas fa-sort-{{ $sort_order == 'asc' ? 'up' : 'down' }}"></i> @endif
                    </a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($organizations as $org)
                @if($subscription = $org->subscriptions->first())
                    <tr>
                        <td>
                            {{-- --- THIS IS THE FIX --- --}}
                            <a href="{{ route('superadmin.subscriptions.history', $org) }}">
                                <strong>{{ $org->name }}</strong>
                            </a>
                            <br>
                            <small class="text-muted">{{ $org->email }}</small>
                        </td>
                        <td>
                            {{ optional($subscription->plan)->name ?? 'Unknown Plan' }}
                        </td>
                        <td>
                            @if($subscription->canceled())
                                <span class="badge badge-warning">Canceled</span>
                            @else
                                <span class="badge badge-success">{{ ucfirst($subscription->stripe_status) }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $org->created_at->format('d M Y') }}
                        </td>
                        <td>
                            @if ($date = $subscription->calculated_ends_at)
                                @if($subscription->canceled())
                                    <span class="text-danger">Ends on {{ $date->format('d M Y') }}</span>
                                @else
                                    Renews on {{ $date->format('d M Y') }}
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($subscription->canceled())
                                <form action="{{ route('superadmin.subscriptions.resume', $org) }}" method="POST" onsubmit="return confirm('Reactivate this subscription?');" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-xs btn-success">Reactivate</button>
                                </form>
                            @else
                                <form action="{{ route('superadmin.subscriptions.cancel', $org) }}" method="POST" onsubmit="return confirm('Cancel this subscription at the end of the current period?');" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-xs btn-danger">Deactivate</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="text-center">No subscribed organizations found for this filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $organizations->appends(request()->query())->links() }}
</div>