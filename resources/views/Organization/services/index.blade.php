@extends('layouts.app')

@section('title', 'Services')

@section('content_header')
    <h1>Services</h1>
@stop

@section('content')
<div class="card card-info card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" id="service-status-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="active-services-tab" data-status="A" data-toggle="pill" href="#services-content" role="tab">Active Services</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="suspended-services-tab" data-status="I" data-toggle="pill" href="#services-content" role="tab">Suspended Services</a>
            </li>
        </ul>
    </div>
    <div class="card-header">
        <h3 class="card-title" id="card-title">All Active Services</h3>
        <div class="card-tools">
            <a href="{{ route('services.create') }}" class="btn btn-info btn-sm">Add New Service</a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif
        
        <div class="mb-3">
            <div class="input-group">
                <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by service name..." value="{{ request('search') }}">
            </div>
        </div>

        <div id="services-table-container">
            @include('Organization.services._services_table', ['services' => $services, 'sort_by' => $sort_by, 'sort_order' => $sort_order])
        </div>
    </div>
</div>

@include('Organization.services._job_modal')

@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    function fetch_services_data(page, sort_by, sort_order, search, status) {
        $('#services-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('services.index') }}",
            data: { 
                page: page, 
                sort_by: sort_by, 
                sort_order: sort_order, 
                search: search,
                status: status 
            },
            success: function(data) {
                $('#services-table-container').html(data);
            }
        });
    }

    function getCurrentStatus() {
        return $('#service-status-tabs .nav-link.active').data('status') || 'A';
    }

    // Tab switching
    $('#service-status-tabs a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        const status = $(e.target).data('status');
        const title = status === 'A' ? 'All Active Services' : 'All Suspended Services';
        $('#card-title').text(title);
        fetch_services_data(1, 'created_at', 'desc', $('#search-input').val(), status);
    });

    $('#search-input').on('keyup', function() {
        clearTimeout(debounceTimer);
        const search = $(this).val();
        debounceTimer = setTimeout(function() {
            fetch_services_data(1, $('#sort_by').val(), $('#sort_order').val(), search, getCurrentStatus());
        }, 300);
    });

    const container = '#services-table-container';

    $(document).on('click', `${container} .sort-link`, function(e) {
        e.preventDefault();
        fetch_services_data(1, $(this).data('sortby'), $(this).data('sortorder'), $('#search-input').val(), getCurrentStatus());
    });

    $(document).on('click', `${container} .pagination a`, function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetch_services_data(page, $('#sort_by').val(), $('#sort_order').val(), $('#search-input').val(), getCurrentStatus());
    });

    $(document).on('keyup', '.job-search-input', function() {
        var searchTerm = $(this).val().toLowerCase();
        $(this).closest('.dropdown-menu').find('.job-link').each(function() {
            var jobName = $(this).text().toLowerCase();
            $(this).toggle(jobName.indexOf(searchTerm) > -1);
        });
    });
    
    $(document).on('click', '.job-search-input', function(e) {
        e.stopPropagation();
    });

    $('#jobModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var serviceId = button.data('service-id');
        var modal = $(this);

        modal.find('.modal-title').text('Add New Job');
        var actionUrl = '/organization/services/' + serviceId + '/jobs';
        modal.find('form').attr('action', actionUrl);
        modal.find('input[name="_method"]').val('POST');
        modal.find('form')[0].reset();
    });

    setTimeout(function() { $('#success-alert').fadeOut('slow'); }, 5000);
});
</script>
@stop