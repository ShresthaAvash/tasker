<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th>Organization</th>
                <th>Plan Name</th>
                <th>Price</th>
                <th>Subscribed On</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($organizations as $org)
                @php $subscription = $org->subscriptions->first(); @endphp
                @if($subscription && $subscription->plan)
                    <tr>
                        <td>
                            <strong>{{ $org->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $org->email }}</small>
                        </td>
                        <td>{{ $subscription->plan->name }}</td>
                        <td>${{ number_format($subscription->plan->price, 2) }}</td>
                        <td>{{ $subscription->created_at->format('d M Y') }}</td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="4" class="text-center">No organizations found for this category.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $organizations->appends(request()->query())->links() }}
</div>