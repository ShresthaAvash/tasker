<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="card-title mb-0">Document Exchange</h4>
    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#documentUploadModal">
        <i class="fas fa-upload"></i> Upload Document for Client
    </button>
</div>

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
        @forelse($client->documents as $document)
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
                    <span class="badge {{ $document->uploader->type === 'C' ? 'badge-primary' : 'badge-info' }}">
                        {{ $document->uploader->name }}
                    </span>
                </td>
                <td>{{ $document->created_at->format('d M Y, h:i A') }}</td>
                <td>
                    @if(in_array(strtolower($document->file_type), ['pdf', 'jpg', 'jpeg', 'png']))
                        <a href="{{ asset('storage/' . $document->file_path) }}" class="btn btn-xs btn-outline-info" target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                    @endif
                    <a href="{{ route('clients.documents.download', $document) }}" class="btn btn-xs btn-secondary">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <form action="{{ route('clients.documents.destroy', $document) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center text-muted p-4">No documents have been shared yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>