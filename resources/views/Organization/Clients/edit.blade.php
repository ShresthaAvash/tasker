@extends('layouts.app')

@section('title', 'Edit Client')

{{-- ✅ Enabling the Select2 Plugin is required for the staff dropdowns --}}
@section('plugins.Select2', true)

@section('content_header')
    <h1>Edit Client: {{ $client->name }}</h1>
@stop

@section('css')
<style>
    /*
     * Custom Dark Tab Styling
     * Mimics the dark sidebar for a more integrated and professional look.
     */
    .card-primary.card-tabs .card-header {
        background-color: #343a40; /* AdminLTE dark sidebar color */
        border-bottom: none; /* Remove the default border */
    }
    .card-primary.card-tabs .nav-link {
        border: 0;
        color: rgba(255, 255, 255, 0.7); /* Light, slightly transparent text */
        transition: all 0.2s ease-in-out;
        border-top: 3px solid transparent; /* Hidden border for alignment */
        margin-bottom: -1px; /* Overlap the card body border slightly */
    }
    .card-primary.card-tabs .nav-link.active {
        background-color: #fff; /* Make active tab background match the card body */
        color: #343a40; /* Dark text for the active tab */
        border-top-color: #007bff; /* Blue indicator line, matching theme */
    }
    .card-primary.card-tabs .nav-link:not(.active):hover {
        color: #ffffff; /* Make text fully white on hover */
        border-top-color: #6c757d; /* A subtle grey indicator on hover */
    }

    /* Pinned Note Styling */
    .pinned-note-bar {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        border-radius: .25rem;
        padding: .75rem 1.25rem;
        margin-bottom: 1rem;
        position: relative;
    }
    .pinned-note-bar .unpin-btn {
        position: absolute;
        top: 5px;
        right: 10px;
    }

    /* Custom styles for the Select2 dropdown to match the theme */
    .select2-container--default .select2-selection--multiple {
        background-color: #fff;
        border-color: #ced4da;
        color: #495057;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border-color: #006fe6;
        color: #fff;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgba(255,255,255,0.7);
    }
    .select2-container--default .select2-search--inline .select2-search__field {
        color: #495057;
    }
    .select2-dropdown {
        border-color: #ced4da;
    }
</style>
@stop

@section('content')

{{-- Pinned Note Display Area --}}
@if($client->pinnedNote)
<div class="pinned-note-bar">
    <form action="{{ route('clients.notes.unpin', $client->pinnedNote) }}" method="POST" class="unpin-btn">@csrf @method('PATCH')<button type="submit" class="btn btn-xs btn-outline-secondary" title="Unpin Note"><i class="fas fa-thumbtack"></i> Unpin</button></form>
    <strong><i class="fas fa-thumbtack mr-1"></i> {{ $client->pinnedNote->title }} ({{ $client->pinnedNote->note_date->format('d M Y') }})</strong>
    <p class="mb-0">{{ $client->pinnedNote->content }}</p>
</div>
@endif

{{-- Success and Error Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="card card-primary card-tabs">
    <div class="card-header p-0 pt-1">
        <ul class="nav nav-tabs" id="client-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" id="general-tab-link" data-toggle="pill" href="#general-tab" role="tab">General</a></li>
            <li class="nav-item"><a class="nav-link" id="contacts-tab-link" data-toggle="pill" href="#contacts-tab" role="tab">Contacts</a></li>
            <li class="nav-item"><a class="nav-link" id="notes-tab-link" data-toggle="pill" href="#notes-tab" role="tab">Notes</a></li>
            <li class="nav-item"><a class="nav-link" id="documents-tab-link" data-toggle="pill" href="#documents-tab" role="tab">Documents</a></li>
            <li class="nav-item"><a class="nav-link" id="services-tab-link" data-toggle="pill" href="#services-tab" role="tab">Services</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="client-tabs-content">
            <!-- General Tab -->
            <div class="tab-pane fade show active" id="general-tab" role="tabpanel">
                <form action="{{ route('clients.update', $client->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="form-group"><label>Client Name</label><input type="text" class="form-control" name="name" value="{{ old('name', $client->name) }}" required></div>
                    <div class="form-group"><label>Client Email</label><input type="email" class="form-control" name="email" value="{{ old('email', $client->email) }}" required></div>
                    <div class="form-group"><label>Client Phone</label><input type="text" class="form-control" name="phone" value="{{ old('phone', $client->phone) }}"></div>
                    <div class="form-group"><label>Client Address</label><textarea class="form-control" name="address">{{ old('address', $client->address) }}</textarea></div>
                    <div class="form-group"><label>Client Photo</label>@if($client->photo)<div><img src="{{ asset('storage/'.$client->photo) }}" alt="Photo" width="100" class="mb-2 img-thumbnail"></div>@endif<input type="file" class="form-control-file" name="photo" accept="image/*"></div>
                    <div class="form-group"><label>Status</label><select class="form-control" name="status" required><option value="A" @if(old('status', $client->status) == 'A') selected @endif>Active</option><option value="I" @if(old('status', $client->status) == 'I') selected @endif>Inactive</option></select></div>
                    <hr><p class="text-muted">Password (leave blank to keep current)</p>
                    <div class="row"><div class="col-md-6"><div class="form-group"><label>New Password</label><input type="password" class="form-control" name="password"></div></div><div class="col-md-6"><div class="form-group"><label>Confirm Password</label><input type="password" class="form-control" name="password_confirmation"></div></div></div>
                    <button type="submit" class="btn btn-primary">Save Changes</button> <a href="{{ route('clients.index') }}" class="btn btn-secondary">Back to List</a>
                </form>
            </div>

            <!-- Contacts Tab -->
            <div class="tab-pane fade" id="contacts-tab" role="tabpanel">
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
            </div>

            <!-- Notes Tab -->
            <div class="tab-pane fade" id="notes-tab" role="tabpanel">
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
            </div>
            
            <!-- Documents Tab -->
            <div class="tab-pane fade" id="documents-tab" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3"><h4 class="card-title mb-0">Client Documents</h4><button class="btn btn-sm btn-success" data-toggle="modal" data-target="#documentModal"><i class="fas fa-upload"></i> Upload New Document</button></div>
                <table class="table table-hover">
                    <thead><tr><th>Name</th><th>Uploaded By</th><th>Uploaded At</th><th>Type</th><th>Size</th><th style="width: 220px;">Actions</th></tr></thead>
                    <tbody>
                        @forelse($client->documents as $document)
                            <tr><td>{{ $document->name }}</td><td>{{ $document->uploader->name ?? 'N/A' }}</td><td>{{ $document->created_at->format('d M Y') }}</td><td><span class="badge badge-secondary">{{ strtoupper($document->file_type) }}</span></td><td>{{ number_format($document->file_size / 1024, 2) }} KB</td><td><a href="{{ asset('storage/' . $document->file_path) }}" class="btn btn-xs btn-secondary" target="_blank">View</a><a href="{{ route('clients.documents.download', $document) }}" class="btn btn-xs btn-info">Download</a><form action="{{ route('clients.documents.destroy', $document) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this document?');">@csrf @method('DELETE')<button type="submit" class="btn btn-xs btn-danger">Delete</button></form></td></tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No documents have been uploaded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Services Tab -->
            <div class="tab-pane fade" id="services-tab" role="tabpanel">
                <form id="service-assignment-form" action="{{ route('clients.services.assign', $client) }}" method="POST">
                    @csrf
                    <!-- Step 1: Service Selection -->
                    <div id="service-selection-step">
                        <h4>1. Select Services</h4>
                        <p>Choose the services you want to activate for this client.</p>
                        <div class="form-group">
                            @forelse($allServices as $service)
                                <div class="custom-control custom-checkbox"><input class="custom-control-input service-checkbox" type="checkbox" id="service_{{ $service->id }}" name="services[]" value="{{ $service->id }}" {{ $client->assignedServices->contains($service) ? 'checked' : '' }}><label for="service_{{ $service->id }}" class="custom-control-label">{{ $service->name }}</label></div>
                            @empty
                                <p class="text-muted">No services have been created yet. <a href="{{ route('services.create') }}">Create one now</a>.</p>
                            @endforelse
                        </div>
                        <button type="button" id="next-to-jobs-btn" class="btn btn-primary">Next <i class="fas fa-arrow-right"></i></button>
                    </div>

                    <!-- Step 2: Job & Task Configuration -->
                    <div id="job-config-step" style="display: none;">
                        <h4>2. Configure Jobs & Tasks</h4>
                        <p>Uncheck any jobs or tasks to exclude them. Assign staff members to each task.</p>
                        <div id="jobs-accordion-container"></div>
                        <hr>
                        <button type="button" id="back-to-services-btn" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Save Assignments</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="contactForm" method="POST" action="">@csrf<input type="hidden" id="contact-method" name="_method" value="POST"><div class="modal-header"><h5 class="modal-title" id="contactModalLabel">Add Contact</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>Name</label><input type="text" id="contact-name" name="name" class="form-control" required></div><div class="form-group"><label>Email</label><input type="email" id="contact-email" name="email" class="form-control" required></div><div class="form-group"><label>Phone (Optional)</label><input type="text" id="contact-phone" name="phone" class="form-control"></div><div class="form-group"><label>Position</label><input type="text" id="contact-position" name="position" class="form-control"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>

<!-- Note Modal -->
<div class="modal fade" id="noteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="noteForm" method="POST" action="">@csrf<input type="hidden" id="note-method" name="_method" value="POST"><div class="modal-header"><h5 class="modal-title" id="noteModalLabel">Add Note</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>Title</label><input type="text" id="note-title" name="title" class="form-control" required></div><div class="form-group"><label>Content</label><textarea id="note-content" name="content" class="form-control" rows="4" required></textarea></div><div class="form-group"><label>Date</label><input type="date" id="note-date" name="note_date" class="form-control" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>

<!-- Document Upload Modal -->
<div class="modal fade" id="documentModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="documentForm" method="POST" action="{{ route('clients.documents.store', $client) }}" enctype="multipart/form-data">@csrf<div class="modal-header"><h5 class="modal-title">Upload Document</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>Document Name</label><input type="text" name="name" class="form-control" required placeholder="e.g., Signed Contract"></div><div class="form-group"><label>File</label><input type="file" name="document_file" class="form-control-file" required><small class="form-text text-muted">Allowed types: PDF, DOCX, PNG, JPG. Max size: 10MB.</small></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Upload</button></div></form></div></div></div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Logic to remember the active tab using localStorage
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('activeClientTab', $(e.target).attr('href'));
    });
    let activeTab = localStorage.getItem('activeClientTab');
    if (activeTab && $('#client-tabs a[href="' + activeTab + '"]').length) {
        $('#client-tabs a[href="' + activeTab + '"]').tab('show');
    }

    // Contact Modal Logic for Create and Edit
    $('#contactModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const action = button.data('action');
        const modal = $(this);
        const form = modal.find('form');
        form[0].reset();
        if (action === 'edit') {
            const contact = button.data('contact');
            modal.find('.modal-title').text('Edit Contact');
            form.attr('action', `/organization/client-contacts/${contact.id}`);
            $('#contact-method').val('PUT');
            $('#contact-name').val(contact.name);
            $('#contact-email').val(contact.email);
            $('#contact-phone').val(contact.phone);
            $('#contact-position').val(contact.position);
        } else {
            modal.find('.modal-title').text('Add New Contact');
            form.attr('action', '{{ route('clients.contacts.store', $client) }}');
            $('#contact-method').val('POST');
        }
    });

    // Note Modal Logic for Create and Edit
    $('#noteModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const action = button.data('action');
        const modal = $(this);
        const form = modal.find('form');
        form[0].reset();
        if (action === 'edit') {
            const note = button.data('note');
            modal.find('.modal-title').text('Edit Note');
            form.attr('action', `/organization/client-notes/${note.id}`);
            $('#note-method').val('PUT');
            $('#note-title').val(note.title);
            $('#note-content').val(note.content);
            $('#note-date').val(note.note_date.split(' ')[0]);
        } else {
            modal.find('.modal-title').text('Add New Note');
            form.attr('action', '{{ route('clients.notes.store', $client) }}');
            $('#note-method').val('POST');
            $('#note-date').val(new Date().toISOString().slice(0, 10));
        }
    });

    // ====================================================================
    // SERVICE ASSIGNMENT JS
    // ====================================================================
    const serviceSelectionStep = $('#service-selection-step');
    const jobConfigStep = $('#job-config-step');
    const jobsContainer = $('#jobs-accordion-container');
    const allStaffData = [ @foreach($allStaff as $staff) { id: '{{ $staff->id }}', text: '{{ $staff->name }}' }, @endforeach ];

    $('#next-to-jobs-btn').on('click', function() {
        const selectedServiceIds = $('.service-checkbox:checked').map((_, el) => $(el).val()).get();
        if (selectedServiceIds.length === 0) {
            alert('Please select at least one service to continue.');
            return;
        }

        jobsContainer.html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p>Loading jobs and tasks...</p></div>');
        serviceSelectionStep.hide();
        jobConfigStep.show();

        $.ajax({
            url: '{{ route('clients.services.getJobs') }}',
            method: 'GET',
            data: { service_ids: selectedServiceIds },
            success: function(jobs) {
                jobsContainer.empty();
                if (jobs.length === 0) {
                    jobsContainer.html('<p class="text-muted">The selected services do not have any jobs configured.</p>');
                    return;
                }
                
                jobs.forEach(job => {
                    let taskHtml = '';
                    if (job.tasks.length > 0) {
                        job.tasks.forEach(task => {
                            taskHtml += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <input type="checkbox" name="tasks[${task.id}]" id="task_${task.id}" checked class="mr-2 task-checkbox">
                                        <label for="task_${task.id}" class="mb-0 font-weight-normal">${task.name}</label>
                                    </div>
                                    <div style="width: 60%;">
                                        <select class="form-control staff-select" name="staff_assignments[${task.id}][]" multiple="multiple" style="width: 100%;"></select>
                                    </div>
                                </li>`;
                        });
                    } else {
                        taskHtml = '<li class="list-group-item text-muted">No tasks in this job.</li>';
                    }

                    const jobHtml = `
                        <div class="card mb-2 shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <button class="btn btn-link text-dark font-weight-bold" type="button" data-toggle="collapse" data-target="#collapse_job_${job.id}">
                                        ${job.name}
                                    </button>
                                </h5>
                            </div>
                            <div id="collapse_job_${job.id}" class="collapse show">
                                <ul class="list-group list-group-flush">${taskHtml}</ul>
                            </div>
                        </div>`;
                    jobsContainer.append(jobHtml);
                });

                $('.staff-select').select2({
                    placeholder: 'Assign Staff Members',
                    data: allStaffData,
                    width: '100%'
                });
            },
            error: () => jobsContainer.html('<p class="text-danger">Failed to load job data. Please try again.</p>')
        });
    });

    $('#back-to-services-btn').on('click', function() {
        jobConfigStep.hide();
        serviceSelectionStep.show();
    });
});
</script>
@stop