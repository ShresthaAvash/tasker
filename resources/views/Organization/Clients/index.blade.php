@extends('layouts.app')

@section('title', 'Clients')

@section('content_header')
    <h1>Clients</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Clients</h3>
        <div class="card-tools">
            <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">Add New Client</a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif

        <!-- Search Input -->
        <div class="mb-3">
            <div class="input-group">
                <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
        </div>

        <!-- This container will be updated by AJAX -->
        <div id="clients-table-container">
            @include('Organization.clients._clients_table', ['clients' => $clients, 'sort_by' => $sort_by, 'sort_order' => $sort_order])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    function fetch_clients_data(page, sort_by, sort_order, search) {
        $('#clients-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');

        $.ajax({
            url: "{{ route('clients.index') }}",
            data: {
                page: page,
                sort_by: sort_by,
                sort_order: sort_order,
                search: search
            },
            success: function(data) {
                $('#clients-table-container').html(data);
            },
            error: function() {
                alert('Could not load client data. Please refresh the page.');
                $('#clients-table-container').html('<p class="text-danger text-center">Failed to load data.</p>');
            }
        });
    }

    // Real-time search with debouncing
    $('#search-input').on('keyup', function() {
        clearTimeout(debounceTimer);
        const search = $(this).val();
        debounceTimer = setTimeout(function() {
            const sort_by = $('#sort_by').val();
            const sort_order = $('#sort_order').val();
            fetch_clients_data(1, sort_by, sort_order, search);
        }, 300);
    });

    // Sorting functionality
    $(document).on('click', '#clients-table-container .sort-link', function(e) {
        e.preventDefault();
        const sort_by = $(this).data('sortby');
        const sort_order = $(this).data('sortorder');
        const search = $('#search-input').val();
        fetch_clients_data(1, sort_by, sort_order, search);
    });

    // ✅ THIS IS THE PAGINATION HANDLER
    // It intercepts clicks on pagination links and makes an AJAX request instead.
    $(document).on('click', '#clients-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        const sort_by = $('#sort_by').val();
        const sort_order = $('#sort_order').val();
        const search = $('#search-input').val();
        fetch_clients_data(page, sort_by, sort_order, search);
    });

    // Hide success alert
    setTimeout(function() {
        $('#success-alert').fadeOut('slow');
    }, 5000);
});
</script>
@stop