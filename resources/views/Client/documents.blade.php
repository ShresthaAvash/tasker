@extends('layouts.app')

@section('title', 'My Documents')

@section('content_header')
    <h1>My Documents</h1>
@stop

@section('css')
<style>
.document-thread {
    list-style-type: none;
    padding: 0;
}
.document-entry {
    display: flex;
    margin-bottom: 1.5rem;
    max-width: 80%;
}
.document-entry .document-bubble {
    background-color: #f1f0f0;
    border-radius: 12px;
    padding: 1rem;
    position: relative;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.document-entry.is-client {
    margin-left: auto;
    flex-direction: row-reverse;
}
.document-entry.is-client .document-bubble {
    background-color: #dcf8c6;
}
.document-entry .uploader-name {
    font-weight: bold;
    margin-bottom: 0.25rem;
}
.document-entry .document-name {
    font-size: 1.1rem;
    font-weight: 500;
}
.document-entry .document-meta {
    font-size: 0.8rem;
    color: #6c757d;
}
</style>
@stop

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Upload a New Document</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('client.documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="name">Document Title</label>
                <input type="text" name="name" class="form-control" placeholder="e.g., March Bank Statement" required>
            </div>
            <div class="form-group">
                <label for="description">Description / Message (Optional)</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Add any context or notes for this document..."></textarea>
            </div>
            <div class="form-group">
                <label for="document_file">File</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="document_file" name="document_file" required>
                    <label class="custom-file-label" for="document_file">Choose file</label>
                </div>
                <small class="form-text text-muted">Max file size: 10MB. Allowed types: PDF, DOCX, XLSX, JPG, PNG.</small>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Document History</h3>
    </div>
    <div class="card-body">
        <ul class="document-thread">
            @forelse($documents as $document)
                <li class="document-entry {{ $document->uploader->id === Auth::id() ? 'is-client' : 'is-organization' }}">
                    <div class="document-bubble">
                        <p class="uploader-name">{{ $document->uploader->id === Auth::id() ? 'You' : $document->uploader->name }}</p>
                        <p class="document-name mb-1">
                            <i class="fas fa-file-alt mr-2"></i>{{ $document->name }}
                        </p>
                        @if($document->description)
                            <p class="mb-2"><em>{{ $document->description }}</em></p>
                        @endif
                        <p class="document-meta mb-2">
                            {{ $document->created_at->format('d M Y, h:i A') }} | {{ strtoupper($document->file_type) }} | {{ number_format($document->file_size / 1024, 1) }} KB
                        </p>
                        
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
                    </div>
                </li>
            @empty
                <li class="text-center text-muted p-4">No documents have been shared yet.</li>
            @endforelse
        </ul>
    </div>
    @if($documents->hasPages())
    <div class="card-footer">
        {{ $documents->links() }}
    </div>
    @endif
</div>
@stop

@section('js')
<script>
    $(function () {
        bsCustomFileInput.init();
    });
</script>
@stop