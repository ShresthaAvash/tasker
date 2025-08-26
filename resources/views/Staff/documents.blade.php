@extends('layouts.app')

@section('title', 'Document Exchange')

@section('content_header')
    <h1>Document Exchange</h1>
@stop

@section('css')
@parent
<style>
    /* Styles are copied from client document view for consistency */
    #documentUploadModal .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .file-drop-zone {
        border: 2px dashed #adb5bd;
        border-radius: .375rem;
        padding: 2.5rem 1rem;
        text-align: center;
        background-color: #f8f9fa;
        cursor: pointer;
        transition: background-color 0.2s ease-in-out, border-color 0.2s ease-in-out;
    }
    .file-drop-zone.is-active {
        background-color: #e9ecef;
        border-color: #007bff;
    }
    .file-drop-zone .file-drop-icon {
        font-size: 2.5rem;
        color: #6c757d;
    }
    .file-drop-zone .file-drop-text {
        color: #495057;
        margin-top: 0.5rem;
    }
    #file-preview-list {
        display: none; /* Hidden by default */
        max-height: 200px; /* Or whatever height you prefer */
        overflow-y: auto;
        margin-top: 1rem;
    }
    .file-preview-item {
        display: flex;
        align-items: center;
        background-color: #e9ecef;
        border-radius: .375rem;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
    }
    .file-preview-icon {
        font-size: 2rem;
        margin-right: 1rem;
        flex-shrink: 0;
        width: 30px;
        text-align: center;
    }
    .file-preview-info {
        text-align: left;
        flex-grow: 1;
        overflow: hidden;
    }
    .file-preview-name {
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .file-preview-size {
        font-size: 0.8rem;
        color: #6c757d;
    }
    .remove-file-btn {
        font-size: 1.2rem;
        line-height: 1;
        color: #dc3545;
        background: none;
        border: none;
        padding: 0 .5rem;
        cursor: pointer;
    }

    .file-preview-item.removing {
        animation: fadeOutUp 0.3s ease forwards;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeOutUp {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-10px); }
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
        <div class="row align-items-center">
            <div class="col-md-6">
                <form id="client-select-form" method="GET" action="{{ route('staff.documents.index') }}">
                    <div class="form-group mb-0">
                        <label for="client_id">Select a Client to View Documents</label>
                        <select name="client_id" id="client_id" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Please select a client --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ ($selectedClient && $selectedClient->id == $client->id) ? 'selected' : '' }}>
                                    {{ $client->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="col-md-6 text-right">
                @if($selectedClient)
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#documentUploadModal">
                        <i class="fas fa-upload"></i> Upload Document for {{ $selectedClient->name }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($selectedClient)
        <div class="card-body p-0">
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
                            $iconClass = 'fa-file-alt';
                            $iconColor = 'text-muted';

                            if (in_array($fileType, ['pdf'])) { $iconClass = 'fa-file-pdf'; $iconColor = 'text-danger'; }
                            elseif (in_array($fileType, ['doc', 'docx'])) { $iconClass = 'fa-file-word'; $iconColor = 'text-primary'; }
                            elseif (in_array($fileType, ['xls', 'xlsx'])) { $iconClass = 'fa-file-excel'; $iconColor = 'text-success'; }
                            elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif'])) { $iconClass = 'fa-file-image'; $iconColor = 'text-info'; }
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
                                @php
                                    $badgeClass = 'badge-secondary';
                                    $uploaderName = $document->uploader->name;
                                    if ($document->uploader->id === Auth::id()) {
                                        $badgeClass = 'badge-primary';
                                        $uploaderName = 'You';
                                    } elseif ($document->uploader->type === 'C') {
                                        $badgeClass = 'badge-info';
                                    } elseif (in_array($document->uploader->type, ['O', 'T'])) {
                                        $badgeClass = 'badge-success';
                                    }
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ $uploaderName }}</span>
                            </td>
                            <td>{{ $document->created_at->format('d M Y, h:i A') }}</td>
                            <td>
                                <a href="{{ route('staff.documents.download', $document) }}" class="btn btn-xs btn-secondary">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                @if($document->uploaded_by_id === Auth::id())
                                    <form action="{{ route('staff.documents.destroy', $document) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted p-4">No documents have been shared for this client yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documents->hasPages())
        <div class="card-footer">
            {{ $documents->appends(['client_id' => $selectedClient->id])->links() }}
        </div>
        @endif
    @else
        <div class="card-body">
            <p class="text-center text-muted">Please select a client to view or upload documents.</p>
        </div>
    @endif
</div>

<!-- Document Upload Modal -->
@if($selectedClient)
<div class="modal fade" id="documentUploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('staff.documents.store', $selectedClient) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Documents for {{ $selectedClient->name }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Document Title</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., March Bank Statement" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description / Message (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Add any context or notes for this document..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Files</label>
                        <div class="file-drop-zone">
                            <i class="fas fa-cloud-upload-alt file-drop-icon"></i>
                            <p class="file-drop-text">Drag & drop files here or <strong>click to browse</strong>.</p>
                        </div>
                        <input type="file" class="d-none" id="document_file" name="document_file[]" multiple required>
                        <div id="file-preview-list"></div>
                        <small class="form-text text-muted mt-2">Max file size: 10MB per file.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" disabled><i class="fas fa-upload mr-1"></i> Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@stop

@section('js')
<script>
    // JS for the uploader modal, copied from client/documents.blade.php
    $(function () {
        const uploadModal = $('#documentUploadModal');
        if (!uploadModal.length) return; // Don't run if modal isn't on the page

        const dropZone = uploadModal.find('.file-drop-zone');
        const fileInput = uploadModal.find('#document_file');
        const filePreviewList = uploadModal.find('#file-preview-list');
        const uploadButton = uploadModal.find('button[type="submit"]');
        let fileStore = new DataTransfer();

        function handleFiles(files) {
            for (const file of files) {
                fileStore.items.add(file);
            }
            updateFileInput();
            renderPreviews();
        }

        function renderPreviews() {
            filePreviewList.empty();
            uploadButton.prop('disabled', fileStore.files.length === 0);

            if (fileStore.files.length === 0) {
                filePreviewList.hide();
                return;
            }

            filePreviewList.show();
            
            for (const file of fileStore.files) {
                const fileType = file.name.split('.').pop().toLowerCase();
                let iconClass = 'fas fa-file-alt', iconColor = 'text-muted';
                if (file.type.startsWith('image/')) { iconClass = 'fas fa-file-image'; iconColor = 'text-info';
                } else if (fileType === 'pdf') { iconClass = 'fas fa-file-pdf'; iconColor = 'text-danger';
                } else if (['doc', 'docx'].includes(fileType)) { iconClass = 'fas fa-file-word'; iconColor = 'text-primary';
                } else if (['xls', 'xlsx'].includes(fileType)) { iconClass = 'fas fa-file-excel'; iconColor = 'text-success'; }
                
                const previewHtml = `
                    <div class="file-preview-item" data-name="${file.name}">
                        <i class="file-preview-icon ${iconClass} ${iconColor}"></i>
                        <div class="file-preview-info">
                            <div class="file-preview-name">${file.name}</div>
                            <div class="file-preview-size">${(file.size / 1024).toFixed(1)} KB</div>
                        </div>
                        <button type="button" class="remove-file-btn">&times;</button>
                    </div>
                `;
                filePreviewList.append(previewHtml);
            }
        }
        
        function updateFileInput() {
            fileInput.prop('files', fileStore.files);
        }
        
        dropZone.on('click', () => fileInput.click());
        dropZone.on('dragover', (e) => { e.preventDefault(); e.stopPropagation(); dropZone.addClass('is-active'); });
        dropZone.on('dragleave', (e) => { e.preventDefault(); e.stopPropagation(); dropZone.removeClass('is-active'); });
        dropZone.on('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.removeClass('is-active');
            handleFiles(e.originalEvent.dataTransfer.files);
        });

        fileInput.on('change', function() {
            handleFiles(this.files);
        });

        filePreviewList.on('click', '.remove-file-btn', function() {
            const itemToRemove = $(this).closest('.file-preview-item');
            const fileName = itemToRemove.data('name');
            
            itemToRemove.addClass('removing');

            setTimeout(() => {
                const newFiles = new DataTransfer();
                for (const file of fileStore.files) {
                    if (file.name !== fileName) {
                        newFiles.items.add(file);
                    }
                }
                fileStore = newFiles;
                updateFileInput();
                itemToRemove.remove();

                if (fileStore.files.length === 0) {
                    filePreviewList.hide();
                    uploadButton.prop('disabled', true);
                }
            }, 300); // Wait for animation to finish
        });
        
        uploadModal.on('hidden.bs.modal', function () {
            fileStore = new DataTransfer();
            updateFileInput();
            renderPreviews();
            $(this).find('form')[0].reset();
        });
    });
</script>
@stop