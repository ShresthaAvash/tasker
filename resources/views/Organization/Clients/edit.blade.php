@extends('layouts.app')

@section('title', 'Edit Client')

@section('plugins.Select2', true)

@section('content_header')
    <h1>Edit Client: {{ $client->name }}</h1>
@stop

@section('css')
    @include('Organization.Clients.partials._styles')
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Client Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('clients.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Client List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    @if($client->pinnedNote)
                    <div class="pinned-note-bar">
                        <form action="{{ route('clients.notes.unpin', $client->pinnedNote) }}" method="POST" class="unpin-btn">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-xs btn-outline-secondary" title="Unpin Note">
                                <i class="fas fa-thumbtack"></i> Unpin
                            </button>
                        </form>
                        <strong>
                            <i class="fas fa-thumbtack mr-1"></i> 
                            {{ $client->pinnedNote->title }} ({{ $client->pinnedNote->note_date->format('d M Y') }})
                        </strong>
                        <p class="mb-0">{{ $client->pinnedNote->content }}</p>
                    </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <ul class="nav nav-tabs" id="client-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" id="general-tab-link" data-toggle="pill" href="#general-tab" role="tab">General</a></li>
                        <li class="nav-item"><a class="nav-link" id="contacts-tab-link" data-toggle="pill" href="#contacts-tab" role="tab">Contacts</a></li>
                        <li class="nav-item"><a class="nav-link" id="notes-tab-link" data-toggle="pill" href="#notes-tab" role="tab">Notes</a></li>
                        <li class="nav-item"><a class="nav-link" id="documents-tab-link" data-toggle="pill" href="#documents-tab" role="tab">Documents</a></li>
                        <li class="nav-item"><a class="nav-link" id="services-tab-link" data-toggle="pill" href="#services-tab" role="tab">Services</a></li>
                    </ul>
                    <div class="tab-content pt-3" id="client-tabs-content">
                        <!-- General Tab -->
                        <div class="tab-pane fade show active" id="general-tab" role="tabpanel">
                            @include('Organization.Clients.partials.general')
                        </div>

                        <!-- Contacts Tab -->
                        <div class="tab-pane fade" id="contacts-tab" role="tabpanel">
                            @include('Organization.Clients.partials.contacts')
                        </div>

                        <!-- Notes Tab -->
                        <div class="tab-pane fade" id="notes-tab" role="tabpanel">
                            @include('Organization.Clients.partials.notes')
                        </div>
                        
                        <!-- Documents Tab -->
                        <div class="tab-pane fade" id="documents-tab" role="tabpanel">
                            @include('Organization.Clients.partials.documents')
                        </div>

                        <!-- Services Tab -->
                        <div class="tab-pane fade" id="services-tab" role="tabpanel">
                            @include('Organization.Clients.partials.services')
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="contactForm" method="POST" action="{{ route('clients.contacts.store', $client) }}">
                @csrf
                <input type="hidden" id="contact-method" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">Add Contact</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Name</label><input type="text" id="contact-name" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" id="contact-email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Phone (Optional)</label><input type="text" id="contact-phone" name="phone" class="form-control"></div>
                    <div class="form-group"><label>Position</label><input type="text" id="contact-position" name="position" class="form-control"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="noteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="noteForm" method="POST" action="{{ route('clients.notes.store', $client) }}">
                @csrf
                <input type="hidden" id="note-method" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="noteModalLabel">Add Note</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group"><label>Title</label><input type="text" id="note-title" name="title" class="form-control" required></div>
                    <div class="form-group"><label>Content</label><textarea id="note-content" name="content" class="form-control" rows="4" required></textarea></div>
                    <div class="form-group"><label>Date</label><input type="date" id="note-date" name="note_date" class="form-control" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-info">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document Upload Modal -->
<div class="modal fade" id="documentUploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('clients.documents.store', $client) }}" method="POST" enctype="multipart/form-data">
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
                        <input type="text" name="name" class="form-control" placeholder="e.g., Q1 Financial Statements" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description / Message (Optional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Add any context or notes for these documents..."></textarea>
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
                    <button type="submit" class="btn btn-info" disabled>
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                    <button id="add-new-job-btn" class="btn btn-sm btn-info">
                        <i class="fas fa-plus"></i> Add Job
                    </button>
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
            <button class="btn btn-sm btn-danger remove-job-btn ml-2">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        <div class="p-3">
            <div class="task-list"></div>
            <button class="btn btn-xs btn-outline-primary add-task-to-job-btn mt-2">
                <i class="fas fa-plus"></i> Add Task
            </button>
        </div>
    </div>
</div>

<!-- --- THIS IS THE NEW MODAL --- -->
<div class="modal fade" id="unassign-task-warning-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-warning"></i> Confirm Action</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>This task has already been assigned. Unchecking it will remove the assignment — reports may be lost and the staff member will no longer be able to work on it.</p>
                <p><strong>Are you sure you want to proceed?</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirm-unassign-btn">Yes, Unassign</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
    @include('Organization.Clients.partials._scripts')
@stop