<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>From</th>
                <th>Email</th>
                <th>Company</th>
                <th>Message</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($messages as $message)
            <tr class="{{ $message->is_read ? '' : 'font-weight-bold' }}">
                <td>
                    @if(!$message->is_read)
                        <span class="badge badge-success">New</span>
                    @endif
                    {{ $message->first_name }} {{ $message->last_name }}
                </td>
                <td>{{ $message->email }}</td>
                <td>{{ $message->company ?? 'N/A' }}</td>
                <td>{{ Str::limit($message->message, 50) }}</td>
                <td>
                    <button class="btn btn-xs btn-primary view-message-btn" data-id="{{ $message->id }}">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">No messages found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $messages->appends(request()->query())->links() }}
</div>