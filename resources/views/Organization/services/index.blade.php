@extends('layouts.app')

@section('title', 'Services')
@section('plugins.Select2', true)

@section('content_header')
    <h1>Services</h1>
@stop

@section('css')
<style>
    .table thead th a.sort-link { color: #0c6ffd; }
    .table thead th a.sort-link i { color: #0c6ffd; }
    
    /* --- CSS FIXES FOR SELECT2 HEIGHT ALIGNMENT --- */
    
    /* 1. Fix for Multi-Select (Client & Status) to look like pills */
    .select2-container .select2-selection--multiple {
        min-height: calc(2.25rem + 2px) !important; /* Standard Bootstrap height (38px) */
        height: auto !important;
        padding: 0 0.375rem !important; /* Match Bootstrap horizontal padding */
        border: 1px solid #ced4da !important;
        background-color: #fff;
    }

    /* 2. Style the "Pills" (Selected items) to look grey/modern */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #e4e6eb !important; /* Light Grey background like your image */
        border: 1px solid #dcdcdc !important;
        color: #495057 !important;
        border-radius: 4px !important;
        margin-top: 5px !important; /* Center vertically */
        margin-right: 5px !important;
        padding: 0 6px !important;
        font-size: 0.9rem;
    }

    /* 3. Fix the "x" remove icon color */
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #6c757d !important;
        margin-right: 5px !important;
        font-weight: bold;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #dc3545 !important; /* Red on hover */
    }

    /* 4. Align the placeholder/search text vertically */
    .select2-container .select2-search--inline .select2-search__field {
        margin-top: 6px !important; /* Align text with pills */
        margin-left: 5px !important;
        color: #495057;
        font-family: inherit;
    }
</style>
@stop

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">All Services</h3>
        <div class="card-tools">
            <a href="{{ route('services.create') }}" class="btn btn-primary btn-sm">Add New Service</a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif
        
        <div class="row mb-3">
            <div class="col-md-5">
                <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by service name..." value="{{ request('search') }}">
            </div>
            
            <!-- UPDATED: Client Filter (Now Multiple) -->
            <div class="col-md-4">
                <select name="client_ids[]" id="client-filter" class="form-control" multiple="multiple">
                    @foreach($clients as $client)
                        {{-- Handle array selection check --}}
                        @php 
                            $selectedClients = request('client_ids') ?? [];
                            if(!is_array($selectedClients)) $selectedClients = explode(',', $selectedClients);
                        @endphp
                        <option value="{{ $client->id }}" {{ in_array($client->id, $selectedClients) ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                 <select name="statuses[]" id="status-filter" class="form-control" multiple="multiple">
                    <option value="A">Active</option>
                    <option value="I">Inactive</option>
                </select>
            </div>
        </div>

        <div id="services-table-container">
            @include('Organization.services._services_table', ['services' => $services, 'sort_by' => $sort_by, 'sort_order' => $sort_order])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    // Initialize Select2 for Status
    $('#status-filter').select2({
        placeholder: 'Filter by Status (All)'
    });
    
    // UPDATED: Initialize Select2 for Client Filter (Multiple)
    $('#client-filter').select2({
        placeholder: 'Filter by Client (All)',
        allowClear: true,
        closeOnSelect: true // Closes dropdown after selecting (optional, feels cleaner)
    });

    function fetch_services_data(page, sort_by, sort_order, search, statuses, client_ids) {
        $('#services-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('services.index') }}",
            data: { 
                page: page, 
                sort_by: sort_by, 
                sort_order: sort_order, 
                search: search, 
                statuses: statuses,
                client_ids: client_ids // UPDATED: Send array
            },
            success: function(data) {
                $('#services-table-container').html(data);
            }
        });
    }

    function trigger_fetch() {
        const search = $('#search-input').val();
        const statuses = $('#status-filter').val();
        const client_ids = $('#client-filter').val(); // UPDATED: Gets array
        const sort_by = $('#sort_by').val() || 'created_at';
        const sort_order = $('#sort_order').val() || 'desc';
        
        fetch_services_data(1, sort_by, sort_order, search, statuses, client_ids);
    }
    
    function trigger_fetch_debounced() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(trigger_fetch, 300);
    }

    $('#search-input').on('keyup', trigger_fetch_debounced);
    $('#status-filter, #client-filter').on('change', trigger_fetch);

    $(document).on('click', '#services-table-container .sort-link', function(e) {
        e.preventDefault();
        $('#sort_by').val($(this).data('sortby'));
        $('#sort_order').val($(this).data('sortorder'));
        trigger_fetch();
    });

    $(document).on('click', '#services-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = new URLSearchParams($(this).attr('href').split('?')[1]).get('page');
        fetch_services_data(
            page, 
            $('#sort_by').val(), 
            $('#sort_order').val(), 
            $('#search-input').val(), 
            $('#status-filter').val(),
            $('#client-filter').val()
        );
    });

    setTimeout(() => $('#success-alert').fadeOut('slow'), 5000);
});
</script>
@stop