{{-- tasker/resources/views/Organization/Clients/partials/contacts.blade.php --}}
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="card-title mb-0">Client Contacts</h4><button class="btn btn-sm btn-success" data-toggle="modal" data-target="#contactModal" data-action="create"><i class="fas fa-plus"></i> Add New Contact</button></div>
<table class="table table-hover">
    <thead><tr><th>Name</th><th>Position</th><th>Email</th><th>Phone</th><th style="width: 150px;">Actions</th></tr></thead>
    <tbody>
        @forelse($client->contacts as $contact)
            <tr><td>{{ $contact->name }}</td><td>{{ $contact->position ?? 'N/A' }}</td><td>{{ $contact->email }}</td><td>{{ $contact->phone ?? 'N/A' }}</td><td><button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#contactModal" data-action="edit" data-contact='{{ $contact->toJson() }}'>Edit</button><form action="{{ route('clients.contacts.destroy', $contact) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this contact?');">@csrf @method('DELETE')<button type="submit" class="btn btn-xs btn-danger">Delete</button></form></td></tr>
        @empty
            <tr><td colspan="5" class="text-center">No contacts added yet.</td></tr>
        @endforelse
    </tbody>
</table>