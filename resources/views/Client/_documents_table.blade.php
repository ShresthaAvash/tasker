<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Uploaded By</th>
                <th>Date</th>
                <th style="width: 220px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($documents as $document)
                @php
                    $fileType = strtolower($document->file_type);
                    $iconClass = 'fa-file-alt'; // Default icon
                    $iconColor = 'text-muted';

                    if (in_array($fileType, ['pdf'])) {
                        $iconClass = 'fa-file-pdf';
                        $iconColor = 'text-danger';
                    } elseif (in_array($fileType, ['doc', 'docx'])) {
                        $iconClass = 'fa-file-word';
                        $iconColor = 'text-primary';
                    } elseif (in_array($fileType, ['xls', 'xlsx'])) {
                        $iconClass = 'fa-file-excel';
                        $iconColor = 'text-success';
                    } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $iconClass = 'fa-file-image';
                        $iconColor = 'text-info';
                    }
                @endphp
                <tr>
                    <td>
                        <i class="fas {{ $iconClass }} {{ $iconColor }} fa-lg mr-2"></i>
                        <strong>{{ $document->name }}</strong>
                        <br>
                        <small class="text-muted">{{ strtoupper($document->file_type) }} - {{ number_format($document->file_size / 1024, 1) }} KB</small>
                    </td>
                    <td>{{ $document->description ?? 'N/A' }}</td>
                    <td>
                        <span class="badge {{ $document->uploader->id === Auth::id() ? 'badge-primary' : 'badge-info' }}">
                            {{ $document->uploader->id === Auth::id() ? 'You' : $document->uploader->name }}
                        </span>
                    </td>
                    <td>{{ $document->created_at->format('d M Y, h:i A') }}</td>
                    <td>
                        @if(in_array(strtolower($document->file_type), ['pdf', 'jpg', 'jpeg', 'png']))
                            <a href="{{ asset('storage/' . $document->file_path) }}" class="btn btn-xs btn-outline-info" target="_blank">
                                <i class="fas fa-eye"></i> View
                            </a>
                        @endif
                        <a href="{{ route('client.documents.download', $document) }}" class="btn btn-xs btn-secondary">
                            <i class="fas fa-download"></i> Download
                        </a>
                        @if($document->uploaded_by_id === Auth::id())
                            <form action="{{ route('client.documents.destroy', $document) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted p-4">No documents found matching your criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($documents->hasPages())
<div class="mt-3">
    {{ $documents->appends(request()->query())->links() }}
</div>
@endif