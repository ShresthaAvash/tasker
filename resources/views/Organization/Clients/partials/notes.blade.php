
{{-- tasker/resources/views/Organization/Clients/partials/notes.blade.php --}}
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="card-title mb-0">Client Notes</h4><button class="btn btn-sm btn-success" data-toggle="modal" data-target="#noteModal" data-action="create"><i class="fas fa-plus"></i> Add New Note</button></div>
<table class="table table-hover">
    <thead><tr><th>Title</th><th>Content</th><th>Date</th><th style="width: 200px;">Actions</th></tr></thead>
    <tbody>
        @forelse($client->notes as $note)
            <tr><td>{{ $note->title }}</td><td>{{ Str::limit($note->content, 70) }}</td><td>{{ $note->note_date->format('d M Y') }}</td><td>@if(!$note->isPinned())<form action="{{ route('clients.notes.pin', $note) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button type="submit" class="btn btn-xs btn-info" title="Pin Note"><i class="fas fa-thumbtack"></i> Pin</button></form>@endif<button class="btn btn-xs btn-warning" data-toggle="modal" data-target="#noteModal" data-action="edit" data-note='{{ $note->toJson() }}'>Edit</button><form action="{{ route('clients.notes.destroy', $note) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this note?');">@csrf @method('DELETE')<button type="submit" class="btn btn-xs btn-danger">Delete</button></form></td></tr>
        @empty
            <tr><td colspan="4" class="text-center">No notes added yet.</td></tr>
        @endforelse
    </tbody>
</table>