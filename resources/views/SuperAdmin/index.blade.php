@extends('layouts.app')

@section('title', 'All Organizations')
@section('plugins.Select2', true)

@section('content_header')
    <h1>All Organizations</h1>
@stop

@section('content')
{{-- MODIFIED: Changed card-info to card-primary for the blue theme --}}
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Organizations</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif
        
        <!-- Filter and Search Row -->
        <div class="row mb-3">
            <div class="col-md-8">
                <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="statuses[]" id="status-filter" class="form-control" multiple="multiple">
                    <option value="A">Active</option>
                    <option value="I">Inactive</option>
                </select>
            </div>
        </div>

        <!-- This container will be updated by AJAX -->
        <div id="organizations-table-container">
            @include('SuperAdmin._organizations_table', ['organizations' => $organizations, 'sort_by' => $sort_by, 'sort_order' => $sort_order])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    $('#status-filter').select2({
        placeholder: 'Filter by Status (All)'
    });

    function fetch_organizations_data(page, sort_by, sort_order, search, statuses) {
        $('#organizations-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('superadmin.organizations.index') }}",
            data: { 
                page: page, 
                sort_by: sort_by, 
                sort_order: sort_order, 
                search: search,
                statuses: statuses
            },
            success: function(data) {
                $('#organizations-table-container').html(data);
            },
            error: function() {
                alert('Could not load organization data. Please refresh the page.');
                $('#organizations-table-container').html('<p class="text-danger text-center">Failed to load data.</p>');
            }
        });
    }

    function trigger_fetch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            const search = $('#search-input').val();
            const statuses = $('#status-filter').val();
            const sort_by = $('#sort_by').val() || 'created_at';
            const sort_order = $('#sort_order').val() || 'desc';
            fetch_organizations_data(1, sort_by, sort_order, search, statuses);
        }, 300);
    }
    
    // Initial load
    trigger_fetch();

    $('#search-input, #status-filter').on('keyup change', trigger_fetch);

    $(document).on('click', '#organizations-table-container .sort-link', function(e) {
        e.preventDefault();
        $('#sort_by').val($(this).data('sortby'));
        $('#sort_order').val($(this).data('sortorder'));
        trigger_fetch();
    });

    $(document).on('click', '#organizations-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetch_organizations_data(page, $('#sort_by').val(), $('#sort_order').val(), $('#search-input').val(), $('#status-filter').val());
    });

    setTimeout(function() { $('#success-alert').fadeOut('slow'); }, 5000);
});
</script>
@stop