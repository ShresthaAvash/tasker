@extends('layouts.app')

@section('title', 'Services')

@section('content_header')
    <h1>Services</h1>
@stop

@section('content')
{{-- --- THIS IS THE FIX --- --}}
{{-- We add 'card-info' and 'card-outline' to style the card --}}
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title">All Services</h3>
        <div class="card-tools">
            {{-- --- THIS IS THE FIX --- --}}
            {{-- We change the button to 'btn-info' to match the theme --}}
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

    function fetch_services_data(page, sort_by, sort_order, search) {
        $('#services-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('services.index') }}",
            data: { page: page, sort_by: sort_by, sort_order: sort_order, search: search },
            success: function(data) {
                $('#services-table-container').html(data);
            }
        });
    }

    $('#search-input').on('keyup', function() {
        clearTimeout(debounceTimer);
        const search = $(this).val();
        debounceTimer = setTimeout(function() {
            fetch_services_data(1, $('#sort_by').val(), $('#sort_order').val(), search);
        }, 300);
    });

    const container = '#services-table-container';

    $(document).on('click', `${container} .sort-link`, function(e) {
        e.preventDefault();
        fetch_services_data(1, $(this).data('sortby'), $(this).data('sortorder'), $('#search-input').val());
    });

    $(document).on('click', `${container} .pagination a`, function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetch_services_data(page, $('#sort_by').val(), $('#sort_order').val(), $('#search-input').val());
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