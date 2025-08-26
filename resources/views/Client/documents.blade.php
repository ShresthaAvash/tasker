@extends('layouts.app')

@section('title', 'My Documents')
@section('plugins.Select2', true)

@section('content_header')
    <h1>My Documents</h1>
@stop

@section('css')
@parent {{-- THIS LINE INHERITS THE PARENT LAYOUT'S CSS --}}
<style>
    /* New Modal Styles */
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
        <h3 class="card-title">Document History</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#documentUploadModal">
                <i class="fas fa-upload"></i> Upload New Document
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-4 align-items-center bg-light p-3 rounded d-print-none">
            <div class="col-md-3">
                <input type="text" id="search-input" class="form-control" placeholder="Search by document name..." value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-2">
                <select id="type-filter" class="form-control" multiple="multiple"></select>
            </div>
            <div class="col-md-2">
                <select id="uploader-filter" class="form-control">
                    <option value="">All Uploaders</option>
                    <option value="client" {{ ($selectedUploadedBy ?? '') === 'client' ? 'selected' : '' }}>Uploaded by Me</option>
                    <option value="organization" {{ ($selectedUploadedBy ?? '') === 'organization' ? 'selected' : '' }}>Uploaded by Organization</option>
                </select>
            </div>
            <div class="col-md-4">
                <div id="dropdown-filters" class="row">
                    <div class="col">
                        <select id="year-filter" class="form-control">
                            @foreach($years as $year)
                                <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col">
                        <select id="month-filter" class="form-control">
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ (string)$num === (string)$currentMonth ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="custom-range-filters" class="row" style="display: none;">
                    <div class="col">
                        <input type="date" id="start-date-filter" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col">
                        <input type="date" id="end-date-filter" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
            <div class="col-md-1 d-flex justify-content-end align-items-center">
                 <div class="custom-control custom-switch pt-1">
                    <input type="checkbox" class="custom-control-input" id="custom-range-switch" {{ $use_custom_range ? 'checked' : '' }}>
                    <label class="custom-control-label" for="custom-range-switch" title="Toggle Date Range"></label>
                </div>
                <button class="btn btn-sm btn-secondary ml-2" id="reset-filters" title="Reset Filters"><i class="fas fa-undo"></i></button>
            </div>
        </div>
        
        <div id="documents-table-container">
            @include('Client._documents_table', ['documents' => $documents])
        </div>
    </div>
</div>

<!-- Document Upload Modal -->
<div class="modal fade" id="documentUploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('client.documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload New Documents</h5>
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
@stop

@section('js')
<script>
    $(function () {
        let debounceTimer;

        // Initialize Select2
        $('#type-filter').select2({
            placeholder: 'Filter by Type',
            data: @json($availableFileTypes)
        }).val(@json($selectedTypes)).trigger('change');

        // Main AJAX function to fetch and update documents
        function fetch_documents_data(page = 1) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                $('#documents-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
                
                let data = {
                    page: page,
                    search: $('#search-input').val(),
                    types: $('#type-filter').val(),
                    uploaded_by: $('#uploader-filter').val(),
                    use_custom_range: $('#custom-range-switch').is(':checked').toString(),
                    start_date: $('#start-date-filter').val(),
                    end_date: $('#end-date-filter').val(),
                    year: $('#year-filter').val(),
                    month: $('#month-filter').val()
                };

                $.ajax({
                    url: "{{ route('client.documents.index') }}",
                    data: data,
                    success: (response) => $('#documents-table-container').html(response),
                    error: () => $('#documents-table-container').html('<p class="text-danger text-center">Failed to load documents.</p>')
                });
            }, 500);
        }

        // Toggle date filter types
        function toggleDateFilters(useCustom) {
            $('#dropdown-filters').toggle(!useCustom);
            $('#custom-range-filters').toggle(useCustom);
        }

        toggleDateFilters($('#custom-range-switch').is(':checked'));
        $('#custom-range-switch').on('change', function() {
            toggleDateFilters(this.checked);
            fetch_documents_data();
        });

        // Reset all filters
        $('#reset-filters').on('click', function() {
            const today = new Date();
            $('#search-input').val('');
            $('#type-filter').val(null).trigger('change');
            $('#uploader-filter').val('');
            $('#custom-range-switch').prop('checked', false);
            $('#year-filter').val(today.getFullYear());
            $('#month-filter').val('all');
            toggleDateFilters(false);
            fetch_documents_data();
        });

        // Event listeners for filters
        $('#search-input, #uploader-filter, #type-filter, #year-filter, #month-filter, #start-date-filter, #end-date-filter').on('keyup change', function() {
            fetch_documents_data(1);
        });

        // AJAX pagination
        $(document).on('click', '#documents-table-container .pagination a', function(e) {
            e.preventDefault();
            const page = $(this).attr('href').split('page=')[1];
            fetch_documents_data(page);
        });

        // Drag-and-drop file uploader script
        const uploadModal = $('#documentUploadModal');
        const dropZone = uploadModal.find('.file-drop-zone');
        const fileInput = uploadModal.find('#document_file');
        const filePreviewList = uploadModal.find('#file-preview-list');
        const uploadButton = uploadModal.find('button[type="submit"]');
        let fileStore = new DataTransfer();

        function handleFiles(files) {
            for (const file of files) { fileStore.items.add(file); }
            updateFileInput();
            renderPreviews();
        }

        function renderPreviews() {
            filePreviewList.empty().hide();
            uploadButton.prop('disabled', fileStore.files.length === 0);
            if (fileStore.files.length === 0) return;
            
            filePreviewList.show();
            for (const file of fileStore.files) {
                const fileType = file.name.split('.').pop().toLowerCase();
                let iconClass = 'fas fa-file-alt', iconColor = 'text-muted';
                if (file.type.startsWith('image/')) { iconClass = 'fas fa-file-image'; iconColor = 'text-info'; } 
                else if (fileType === 'pdf') { iconClass = 'fas fa-file-pdf'; iconColor = 'text-danger'; } 
                else if (['doc', 'docx'].includes(fileType)) { iconClass = 'fas fa-file-word'; iconColor = 'text-primary'; } 
                else if (['xls', 'xlsx'].includes(fileType)) { iconClass = 'fas fa-file-excel'; iconColor = 'text-success'; }
                
                const previewHtml = `<div class="file-preview-item" data-name="${file.name}"><i class="file-preview-icon ${iconClass} ${iconColor}"></i><div class="file-preview-info"><div class="file-preview-name">${file.name}</div><div class="file-preview-size">${(file.size / 1024).toFixed(1)} KB</div></div><button type="button" class="remove-file-btn">&times;</button></div>`;
                filePreviewList.append(previewHtml);
            }
        }
        
        function updateFileInput() { fileInput.prop('files', fileStore.files); }
        
        dropZone.on('click', () => fileInput.click());
        dropZone.on('dragover', (e) => { e.preventDefault(); e.stopPropagation(); dropZone.addClass('is-active'); });
        dropZone.on('dragleave', (e) => { e.preventDefault(); e.stopPropagation(); dropZone.removeClass('is-active'); });
        dropZone.on('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.removeClass('is-active');
            handleFiles(e.originalEvent.dataTransfer.files);
        });
        fileInput.on('change', function() { handleFiles(this.files); });

        filePreviewList.on('click', '.remove-file-btn', function() {
            const itemToRemove = $(this).closest('.file-preview-item');
            const fileName = itemToRemove.data('name');
            itemToRemove.addClass('removing');
            setTimeout(() => {
                const newFiles = new DataTransfer();
                for (const file of fileStore.files) {
                    if (file.name !== fileName) { newFiles.items.add(file); }
                }
                fileStore = newFiles;
                updateFileInput();
                renderPreviews();
            }, 300);
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