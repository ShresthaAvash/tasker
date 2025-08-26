@extends('layouts.app')

@section('title', 'Staff')
@section('plugins.Select2', true)

@section('content_header')
    <h1>Staff Members</h1>
@stop

@section('css')
<style>
    /* Change the color of the sortable table headers to the new theme blue */
    .table thead th a.sort-link {
        color: #0c6ffd;
    }
    .table thead th a.sort-link i {
        color: #0c6ffd;
    }
</style>
@stop

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">All Staff</h3>
        <div class="card-tools">
            <a href="{{ route('staff.create') }}" class="btn btn-primary btn-sm">Add New Staff</a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif
        
        <!-- Filter and Search Row -->
        <div class="row mb-3">
            <div class="col-md-5">
                <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="designation_id" id="designation-filter" class="form-control">
                    <option value="">All Designations</option>
                    @foreach($designations as $designation)
                        <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                            {{ $designation->name }}
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

        <!-- This container will be updated by AJAX -->
        <div id="staff-table-container">
            @include('Organization.staff._staff_table', ['staff' => $staff, 'sort_by' => $sort_by, 'sort_order' => $sort_order])
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

    function fetch_staff_data(page, sort_by, sort_order, search, designation_id, statuses) {
        $('#staff-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('staff.index') }}",
            data: { 
                page: page, 
                sort_by: sort_by, 
                sort_order: sort_order, 
                search: search, 
                designation_id: designation_id,
                statuses: statuses
            },
            success: function(data) { $('#staff-table-container').html(data); },
            error: function() {
                alert('Could not load staff data. Please refresh the page.');
                $('#staff-table-container').html('<p class="text-danger text-center">Failed to load data.</p>');
            }
        });
    }

    function trigger_fetch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            const search = $('#search-input').val();
            const designation_id = $('#designation-filter').val();
            const statuses = $('#status-filter').val();
            const sort_by = $('#sort_by').val() || 'created_at';
            const sort_order = $('#sort_order').val() || 'desc';
            fetch_staff_data(1, sort_by, sort_order, search, designation_id, statuses);
        }, 300);
    }
    
    // Initial load
    trigger_fetch();

    $('#search-input, #designation-filter, #status-filter').on('keyup change', trigger_fetch);

    $(document).on('click', '#staff-table-container .sort-link', function(e) {
        e.preventDefault();
        const sort_by = $(this).data('sortby');
        const sort_order = $(this).data('sortorder');
        const search = $('#search-input').val();
        const designation_id = $('#designation-filter').val();
        const statuses = $('#status-filter').val();
        fetch_staff_data(1, sort_by, sort_order, search, designation_id, statuses);
    });

    $(document).on('click', '#staff-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        const sort_by = $('#sort_by').val();
        const sort_order = $('#sort_order').val();
        const search = $('#search-input').val();
        const designation_id = $('#designation-filter').val();
        const statuses = $('#status-filter').val();
        fetch_staff_data(page, sort_by, sort_order, search, designation_id, statuses);
    });

    setTimeout(function() { $('#success-alert').fadeOut('slow'); }, 5000);
});
</script>
@stop