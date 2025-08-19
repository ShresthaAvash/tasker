@extends('layouts.app')

@section('title', 'Edit Client')

@section('plugins.Select2', true)

@section('content_header')
    <h1>Edit Client: {{ $client->name }}</h1>
@stop

@section('css')
<style>
    /* --- THIS IS THE FIX --- */
    /* Updated all .card-primary selectors to .card-info and changed color codes */
    .card-info.card-tabs .card-header { background-color: #17a2b8; /* AdminLTE info color */ border-bottom: none; }
    .card-info.card-tabs .nav-link { border: 0; color: rgba(255, 255, 255, 0.8); transition: all 0.2s ease-in-out; border-top: 3px solid transparent; margin-bottom: -1px; }
    .card-info.card-tabs .nav-link.active { background-color: #fff; color: #17a2b8; border-top-color: #17a2b8; /* AdminLTE info color */ }
    .card-info.card-tabs .nav-link:not(.active):hover { color: #ffffff; border-top-color: #138496; /* A slightly darker info color for hover */ }
    .pinned-note-bar { background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: .25rem; padding: .75rem 1.25rem; margin-bottom: 1rem; position: relative; }
    .pinned-note-bar .unpin-btn { position: absolute; top: 5px; right: 10px; }
    .select2-container--default .select2-selection--multiple { background-color: #fff; border-color: #ced4da; color: #495057; }
    /* --- THIS IS THE FIX --- */
    /* Updated select2 choice color to match theme */
    .select2-container--default .select2-selection--multiple .select2-selection__choice { background-color: #17a2b8; border-color: #138496; color: #fff; }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove { color: rgba(255,255,255,0.7); }
    .select2-container--default .select2-search--inline .select2-search__field { color: #495057; }
    .select2-dropdown { border-color: #ced4da; }
    #createServiceModal .job-block { border: 1px solid #e9ecef; border-radius: 5px; margin-bottom: 1rem; }
    #createServiceModal .job-header { background-color: #f8f9fa; padding: 0.75rem 1.25rem; border-bottom: 1px solid #e9ecef; }
    #createServiceModal .task-item { border-top: 1px solid #f1f1f1; padding: 0.75rem 1.25rem; }
    .master-checkbox-label { font-weight: normal !important; margin-left: 5px; }
</style>
@stop

@section('content')

@if($client->pinnedNote)
<div class="pinned-note-bar">
    <form action="{{ route('clients.notes.unpin', $client->pinnedNote) }}" method="POST" class="unpin-btn">@csrf @method('PATCH')<button type="submit" class="btn btn-xs btn-outline-secondary" title="Unpin Note"><i class="fas fa-thumbtack"></i> Unpin</button></form>
    <strong><i class="fas fa-thumbtack mr-1"></i> {{ $client->pinnedNote->title }} ({{ $client->pinnedNote->note_date->format('d M Y') }})</strong>
    <p class="mb-0">{{ $client->pinnedNote->content }}</p>
</div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

{{-- --- THIS IS THE FIX --- --}}
{{-- Changed card-primary to card-info --}}
<div class="card card-info card-tabs">
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
                    {{-- --- THIS IS THE FIX --- --}}
                    {{-- Changed btn-primary to btn-info --}}
                    <button type="submit" class="btn btn-info">Save Changes</button> <a href="{{ route('clients.index') }}" class="btn btn-secondary">Back to List</a>
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
                @if($client->assignedServices->isNotEmpty())
                <script>
                    $(function() {
                        $('#service-selection-step').hide();
                        $('#job-config-step').show();
                        $('#next-to-jobs-btn').trigger('click');
                    });
                </script>
                @endif
                <form id="service-assignment-form" action="{{ route('clients.services.assign', $client) }}" method="POST">
                    @csrf
                    <div id="service-selection-step">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4>1. Select Services</h4>
                                <p>Choose the services you want to activate for this client.</p>
                            </div>
                            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#createServiceModal">
                                <i class="fas fa-plus"></i> Create New Service
                            </button>
                        </div>
                        <hr>
                        <div class="form-group" id="service-checkbox-list">
                            @forelse($allServices as $service)
                                <div class="custom-control custom-checkbox"><input class="custom-control-input service-checkbox" type="checkbox" id="service_{{ $service->id }}" name="services[]" value="{{ $service->id }}" {{ $client->assignedServices->contains($service) ? 'checked' : '' }}><label for="service_{{ $service->id }}" class="custom-control-label">{{ $service->name }}</label></div>
                            @empty
                                <p class="text-muted">No services have been created yet. <a href="{{ route('services.create') }}">Create one now</a>.</p>
                            @endforelse
                        </div>
                        {{-- --- THIS IS THE FIX --- --}}
                        {{-- Changed btn-primary to btn-info --}}
                        <button type="button" id="next-to-jobs-btn" class="btn btn-info">Next <i class="fas fa-arrow-right"></i></button>
                    </div>

                    <div id="job-config-step" style="display: none;">
                        <h4>2. Configure Jobs & Tasks</h4>
                        <p>Uncheck any jobs or tasks to exclude them. Assign staff members to each task.</p>
                        <div id="jobs-accordion-container" class="accordion"></div>
                        <hr>
                        <button type="button" id="back-to-services-btn" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</button>
                        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Save Assignments</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="contactModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="contactForm" method="POST" action="{{ route('clients.contacts.store', $client) }}">@csrf<input type="hidden" id="contact-method" name="_method" value="POST"><div class="modal-header"><h5 class="modal-title" id="contactModalLabel">Add Contact</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>Name</label><input type="text" id="contact-name" name="name" class="form-control" required></div><div class="form-group"><label>Email</label><input type="email" id="contact-email" name="email" class="form-control" required></div><div class="form-group"><label>Phone (Optional)</label><input type="text" id="contact-phone" name="phone" class="form-control"></div><div class="form-group"><label>Position</label><input type="text" id="contact-position" name="position" class="form-control"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-info">Save</button></div></form></div></div></div>
<div class="modal fade" id="noteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="noteForm" method="POST" action="{{ route('clients.notes.store', $client) }}">@csrf<input type="hidden" id="note-method" name="_method" value="POST"><div class="modal-header"><h5 class="modal-title" id="noteModalLabel">Add Note</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>Title</label><input type="text" id="note-title" name="title" class="form-control" required></div><div class="form-group"><label>Content</label><textarea id="note-content" name="content" class="form-control" rows="4" required></textarea></div><div class="form-group"><label>Date</label><input type="date" id="note-date" name="note_date" class="form-control" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-info">Save</button></div></form></div></div></div>
<div class="modal fade" id="documentModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form id="documentForm" method="POST" action="{{ route('clients.documents.store', $client) }}" enctype="multipart/form-data">@csrf<div class="modal-header"><h5 class="modal-title">Upload Document</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><div class="form-group"><label>Document Name</label><input type="text" name="name" class="form-control" required placeholder="e.g., Signed Contract"></div><div class="form-group"><label>File</label><input type="file" name="document_file" class="form-control-file" required><small class="form-text text-muted">Allowed types: PDF, DOCX, PNG, JPG. Max size: 10MB.</small></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button><button type="submit" class="btn btn-info">Upload</button></div></form></div></div></div>

@include('Organization.services._task_modal', ['designations' => $designations])

<div class="modal fade" id="createServiceModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Service</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="service-creation-feedback" class="alert" style="display:none;"></div>
                <div class="form-group">
                    <label>Service Name</label>
                    <input type="text" id="new-service-name" class="form-control" placeholder="e.g., Annual Accounts & Tax Return">
                </div>
                <div class="form-group">
                    <label>Service Description</label>
                    <textarea id="new-service-description" class="form-control" rows="2"></textarea>
                </div>
                <hr>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Jobs</h5>
                    <button id="add-new-job-btn" class="btn btn-sm btn-info"><i class="fas fa-plus"></i> Add Job</button>
                </div>
                <div id="service-jobs-container">
                    <p class="text-muted text-center">No jobs added yet.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" id="save-new-service-btn" class="btn btn-primary">Save Service and Assign</button>
            </div>
        </div>
    </div>
</div>

<div id="job-template" style="display: none;">
    <div class="job-block">
        <div class="job-header d-flex justify-content-between align-items-center">
            <input type="text" class="form-control job-name-input" placeholder="Enter Job Name">
            <button class="btn btn-sm btn-danger remove-job-btn ml-2"><i class="fas fa-trash"></i></button>
        </div>
        <div class="p-3">
            <div class="task-list"></div>
            <button class="btn btn-xs btn-outline-primary add-task-to-job-btn mt-2"><i class="fas fa-plus"></i> Add Task</button>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {

    $('#contactModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const action = button.data('action');
        const modal = $(this);
        const form = modal.find('form');
        
        form[0].reset();
        modal.find('.modal-title').text('Add New Contact');
        form.attr('action', '{{ route('clients.contacts.store', $client) }}');
        $('#contact-method').val('POST');
        
        if (action === 'edit') {
            const contact = button.data('contact');
            modal.find('.modal-title').text('Edit Contact');
            form.attr('action', `/organization/client-contacts/${contact.id}`);
            $('#contact-method').val('PUT');
            $('#contact-name').val(contact.name);
            $('#contact-email').val(contact.email);
            $('#contact-phone').val(contact.phone);
            $('#contact-position').val(contact.position);
        }
    });

    $('#noteModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const action = button.data('action');
        const modal = $(this);
        const form = modal.find('form');
        
        form[0].reset();
        modal.find('.modal-title').text('Add New Note');
        form.attr('action', '{{ route('clients.notes.store', $client) }}');
        $('#note-method').val('POST');
        $('#note-date').val(new Date().toISOString().slice(0, 10));

        if (action === 'edit') {
            const note = button.data('note');
            modal.find('.modal-title').text('Edit Note');
            form.attr('action', `/organization/client-notes/${note.id}`);
            $('#note-method').val('PUT');
            $('#note-title').val(note.title);
            $('#note-content').val(note.content);
            $('#note-date').val(note.note_date.split(' ')[0]);
        }
    });

    const serviceSelectionStep = $('#service-selection-step');
    const jobConfigStep = $('#job-config-step');
    const jobsContainer = $('#jobs-accordion-container');
    const allStaffData = {!! $allStaffJson !!};
    const clientTasks = {!! json_encode($client->assignedTasks->keyBy('task_template_id')) !!};

    // --- DEFINITIVE FIX FOR HIERARCHICAL DISPLAY ---
    function renderJobsAndTasks(jobs) {
        jobsContainer.empty();
        if (!jobs || jobs.length === 0) {
            jobsContainer.html('<p class="text-muted text-center p-3">The selected services do not have any jobs or tasks configured.</p>');
            return;
        }

        const jobsByService = jobs.reduce((acc, job) => {
            const serviceId = job.service.id;
            if (!acc[serviceId]) {
                acc[serviceId] = { name: job.service.name, jobs: [] };
            }
            acc[serviceId].jobs.push(job);
            return acc;
        }, {});

        Object.entries(jobsByService).forEach(([serviceId, serviceGroup]) => {
            let jobsHtml = '';
            serviceGroup.jobs.forEach(job => {
                let taskHtml = '';
                if (job.tasks && job.tasks.length > 0) {
                    job.tasks.forEach(task => {
                        const assignedStaffIds = clientTasks[task.id] ? clientTasks[task.id].staff.map(s => s.id) : [];
                        const isChecked = !clientTasks.hasOwnProperty(task.id) || clientTasks[task.id] ? 'checked' : '';
                        taskHtml += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input task-checkbox" name="tasks[${task.id}]" id="task_${task.id}" ${isChecked} data-job-id="${job.id}" data-service-id="${serviceId}">
                                    <label class="custom-control-label font-weight-normal" for="task_${task.id}">${task.name}</label>
                                </div>
                                <div style="width: 50%;">
                                    <select class="form-control staff-select" name="staff_assignments[${task.id}][]" multiple="multiple" style="width: 100%;" data-assigned-staff='${JSON.stringify(assignedStaffIds)}'></select>
                                </div>
                            </li>`;
                    });
                } else { taskHtml = '<li class="list-group-item text-muted">No tasks in this job.</li>'; }
                
                jobsHtml += `
                    <div class="card mb-2">
                        <div class="card-header bg-light">
                            <div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input job-master-checkbox" id="job_master_${job.id}" data-job-id="${job.id}" data-service-id="${serviceId}">
                                <label class="custom-control-label master-checkbox-label" for="job_master_${job.id}">
                                    <a href="#collapse_job_${job.id}" class="text-dark font-weight-bold" data-toggle="collapse">${job.name}</a>
                                </label>
                            </div>
                        </div>
                        <div id="collapse_job_${job.id}" class="collapse">
                            <ul class="list-group list-group-flush">${taskHtml}</ul>
                        </div>
                    </div>`;
            });

            const serviceHtml = `
                <div class="card mb-3 shadow-sm">
                    <div class="card-header" style="background-color: #e3f2fd;">
                         <div class="custom-control custom-checkbox d-inline-block">
                            <input type="checkbox" class="custom-control-input service-master-checkbox" id="service_master_${serviceId}" data-service-id="${serviceId}">
                            <label class="custom-control-label master-checkbox-label" for="service_master_${serviceId}">
                                <a href="#collapse_service_${serviceId}" class="text-dark font-weight-bold" data-toggle="collapse" style="font-size: 1.2rem;">
                                    Service: ${serviceGroup.name}
                                </a>
                            </label>
                        </div>
                    </div>
                    <div id="collapse_service_${serviceId}" class="collapse show">
                        <div class="card-body">${jobsHtml}</div>
                    </div>
                </div>`;
            jobsContainer.append(serviceHtml);
        });
        
        $('.staff-select').each(function() {
            $(this).select2({ placeholder: 'Assign Staff', data: allStaffData, width: '100%' }).val(JSON.parse($(this).attr('data-assigned-staff') || '[]')).trigger('change');
        });

        // Initial check for master checkboxes
        $('.job-master-checkbox, .service-master-checkbox').each(function() {
            updateParentCheckboxState($(this));
        });
    }
    
    $('#next-to-jobs-btn').on('click', function() {
        const selectedServiceIds = $('.service-checkbox:checked').map((_, el) => $(el).val()).get();
        jobsContainer.html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        serviceSelectionStep.hide(); jobConfigStep.show();
        if (selectedServiceIds.length === 0) { renderJobsAndTasks([]); return; }
        $.ajax({ url: '{{ route('clients.services.getJobs') }}', method: 'GET', data: { service_ids: selectedServiceIds }, success: jobs => renderJobsAndTasks(jobs), error: () => jobsContainer.html('<p class="text-danger text-center p-3">Failed to load job data.</p>') });
    });
    $('#back-to-services-btn').on('click', () => { jobConfigStep.hide(); serviceSelectionStep.show(); });

    // --- CHECKBOX LOGIC ---
    function updateParentCheckboxState(checkbox) {
        const isService = checkbox.hasClass('service-master-checkbox');
        const scope = isService ? checkbox.closest('.card') : checkbox.closest('.card').find('.collapse');
        const childSelector = isService ? '.job-master-checkbox' : '.task-checkbox';
        
        const children = scope.find(childSelector);
        const checkedChildren = children.filter(':checked');
        
        checkbox.prop('checked', children.length > 0 && children.length === checkedChildren.length);
        checkbox.prop('indeterminate', checkedChildren.length > 0 && checkedChildren.length < children.length);
    }
    
    jobsContainer.on('change', '.service-master-checkbox', function() {
        const serviceId = $(this).data('service-id');
        const isChecked = $(this).prop('checked');
        $(`[data-service-id="${serviceId}"]`).prop('checked', isChecked).prop('indeterminate', false);
    });

    jobsContainer.on('change', '.job-master-checkbox', function() {
        const jobId = $(this).data('job-id');
        const isChecked = $(this).prop('checked');
        $(`.task-checkbox[data-job-id="${jobId}"]`).prop('checked', isChecked);
        updateParentCheckboxState($(`#service_master_${$(this).data('service-id')}`));
    });

    jobsContainer.on('change', '.task-checkbox', function() {
        const jobId = $(this).data('job-id');
        updateParentCheckboxState($(`#job_master_${jobId}`));
        updateParentCheckboxState($(`#service_master_${$(this).data('service-id')}`));
    });


    // --- Logic for the "Create Service" Modal ---
    let jobCounter = 0;
    
    $('#add-new-job-btn').on('click', function() {
        if ($('#service-jobs-container .text-muted').length) $('#service-jobs-container').empty();
        jobCounter++;
        const jobTemplate = $('#job-template .job-block').clone();
        jobTemplate.attr('data-job-id', jobCounter);
        jobTemplate.find('.add-task-to-job-btn').data('job-id', jobCounter);
        $('#service-jobs-container').append(jobTemplate);
    });
    
    $('#service-jobs-container').on('click', '.remove-job-btn', function() {
        $(this).closest('.job-block').remove();
        if ($('#service-jobs-container').children().length === 0) {
            $('#service-jobs-container').html('<p class="text-muted text-center">No jobs added yet.</p>');
        }
    });

    $('#service-jobs-container').on('click', '.add-task-to-job-btn', function() {
        const jobId = $(this).data('job-id');
        const taskModal = $('#taskModal');
        taskModal.data('target-job-id', jobId);
        taskModal.modal('show');
    });

    // --- DEFINITIVE FIX for RECURRING TOGGLE ---
    $('#taskModal').on('show.bs.modal', function (event) {
        const form = $(this).find('form');
        form[0].reset();

        function toggleRecurringFields() {
            if ($('#is_recurring').is(':checked')) {
                $('#recurring-options').slideDown();
                $('#task-end').prop('required', true);
            } else {
                $('#recurring-options').slideUp();
                $('#task-end').prop('required', false);
            }
        }
        
        // We must re-attach the event listener every time the modal opens.
        $('#is_recurring').off('change').on('change', toggleRecurringFields);
        toggleRecurringFields(); // Run once on open to set the initial state.
    });

    $('#taskModal form').on('submit', function(e) {
        const targetJobId = $('#taskModal').data('target-job-id');
        if (!targetJobId) return;
        
        e.preventDefault();
        const form = $(this);
        const taskData = {
            name: form.find('[name="name"]').val(), description: form.find('[name="description"]').val(),
            start: form.find('[name="start"]').val(), end: form.find('[name="end"]').val(),
            is_recurring: form.find('[name="is_recurring"]').is(':checked'),
            recurring_frequency: form.find('[name="recurring_frequency"]').val(),
            staff_designation_id: form.find('[name="staff_designation_id"]').val()
        };
        const taskHtml = `<div class="task-item" data-task-data='${JSON.stringify(taskData)}'><span>${taskData.name}</span><button type="button" class="btn btn-xs btn-danger float-right remove-task-btn"><i class="fas fa-times"></i></button></div>`;
        
        $(`.job-block[data-job-id="${targetJobId}"]`).find('.task-list').append(taskHtml);
        
        $('#taskModal').modal('hide').removeData('target-job-id');
        form[0].reset();
    });
    
    $('#service-jobs-container').on('click', '.remove-task-btn', function() {
        $(this).closest('.task-item').remove();
    });

    $('#save-new-service-btn').on('click', function() {
        const button = $(this);
        const feedback = $('#service-creation-feedback');
        const serviceData = { name: $('#new-service-name').val(), description: $('#new-service-description').val(), jobs: [] };
        $('#service-jobs-container .job-block').each(function() {
            const jobBlock = $(this);
            const jobData = { name: jobBlock.find('.job-name-input').val(), tasks: [] };
            jobBlock.find('.task-item').each(function() { jobData.tasks.push($(this).data('task-data')); });
            serviceData.jobs.push(jobData);
        });
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        feedback.hide().removeClass('alert-success alert-danger');
        $.ajax({
            // --- MODIFIED AJAX URL TO MATCH NEW ROUTE ---
            url: '{{ route("clients.services.storeForClient", $client) }}',
            method: 'POST', data: JSON.stringify(serviceData), contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                const newCheckbox = `<div class="custom-control custom-checkbox"><input class="custom-control-input service-checkbox" type="checkbox" id="service_${response.service.id}" name="services[]" value="${response.service.id}" checked><label for="service_${response.service.id}" class="custom-control-label">${response.service.name}</label></div>`;
                $('#service-checkbox-list').append(newCheckbox);
                $('#createServiceModal').modal('hide');
            },
            error: function(xhr) {
                let errorMsg = 'An unknown error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                feedback.addClass('alert-danger').text(errorMsg).fadeIn();
            },
            complete: function() {
                button.prop('disabled', false).text('Save Service and Assign');
            }
        });
    });

    // --- Z-INDEX FIX FOR STACKED MODALS ---
    $(document).on('show.bs.modal', '.modal', function () {
        const zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(() => {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    $(document).on('hidden.bs.modal', '.modal', function () {
        if ($('.modal:visible').length > 0) {
            setTimeout(() => {
                $(document.body).addClass('modal-open');
            }, 0);
        }
    });
});
</script>
@stop