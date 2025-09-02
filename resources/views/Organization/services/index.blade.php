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
            <div class="col-md-8">
                <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by service name..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
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

    $('#status-filter').select2({
        placeholder: 'Filter by Status (All)'
    });

    function fetch_services_data(page, sort_by, sort_order, search, statuses) {
        $('#services-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('services.index') }}",
            data: { page, sort_by, sort_order, search, statuses },
            success: function(data) {
                $('#services-table-container').html(data);
            }
        });
    }

    function trigger_fetch() {
        const search = $('#search-input').val();
        const statuses = $('#status-filter').val();
        const sort_by = $('#sort_by').val() || 'created_at';
        const sort_order = $('#sort_order').val() || 'desc';
        fetch_services_data(1, sort_by, sort_order, search, statuses);
    }
    
    function trigger_fetch_debounced() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(trigger_fetch, 300);
    }

    $('#search-input').on('keyup', trigger_fetch_debounced);
    $('#status-filter').on('change', trigger_fetch);

    $(document).on('click', '#services-table-container .sort-link', function(e) {
        e.preventDefault();
        $('#sort_by').val($(this).data('sortby'));
        $('#sort_order').val($(this).data('sortorder'));
        trigger_fetch();
    });

    $(document).on('click', '#services-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = new URLSearchParams($(this).attr('href').split('?')[1]).get('page');
        fetch_services_data(page, $('#sort_by').val(), $('#sort_order').val(), $('#search-input').val(), $('#status-filter').val());
    });

    setTimeout(() => $('#success-alert').fadeOut('slow'), 5000);
});
</script>
@stop