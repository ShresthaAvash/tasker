@extends('layouts.app')

@section('title', 'Subscribed Organizations')

@section('content_header')
    <h1>Subscribed Organizations</h1>
@stop

@section('css')
<style>
    .card-primary.card-tabs .nav-tabs .nav-link {
        color: #343a40; /* Dark text for inactive tabs */
        border-top: 3px solid transparent;
        margin-top: -3px;
    }
    .card-primary.card-tabs .nav-tabs .nav-link.active {
        background-color: #007bff !important;
        border-color: #007bff #007bff #007bff !important;
        color: #ffffff !important; /* White text for active tab */
    }
</style>
@stop

@section('content')
{{-- MODIFIED: Changed card-info to card-primary for the blue theme line --}}
<div class="card card-primary card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" id="subscription-status-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="active-subs-tab" data-status="active" data-toggle="pill" href="#subs-content" role="tab">Active Subscriptions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="deactivated-subs-tab" data-status="deactivated" data-toggle="pill" href="#subs-content" role="tab">Deactivated Subscriptions</a>
            </li>
        </ul>
    </div>
    <div class="card-header">
        <h3 class="card-title" id="card-title">All Active & Trialing Subscriptions</h3>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" id="error-alert">{{ session('error') }}</div>
        @endif
        
        <div class="mb-3">
            <div class="input-group">
                <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by organization name or email..." value="{{ request('search') }}">
            </div>
        </div>

        <div id="subscribed-table-container">
            @include('SuperAdmin.subscriptions._subscribed_table', ['organizations' => $organizations, 'sort_by' => $sort_by, 'sort_order' => $sort_order])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    function fetch_data(page, sort_by, sort_order, search, status) {
        $('#subscribed-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('superadmin.subscriptions.subscribed') }}",
            data: { page: page, sort_by: sort_by, sort_order: sort_order, search: search, status: status },
            success: function(data) {
                $('#subscribed-table-container').html(data);
            },
            error: function() {
                alert('Could not load data. Please refresh the page.');
                $('#subscribed-table-container').html('<p class="text-danger text-center">Failed to load data.</p>');
            }
        });
    }
    
    function getCurrentStatus() {
        return $('#subscription-status-tabs .nav-link.active').data('status') || 'active';
    }

    $('#subscription-status-tabs a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        const status = $(e.target).data('status');
        const title = status === 'active' ? 'All Active & Trialing Subscriptions' : 'All Deactivated Subscriptions';
        $('#card-title').text(title);
        fetch_data(1, 'created_at', 'desc', $('#search-input').val(), status);
    });

    $('#search-input').on('keyup', function() {
        clearTimeout(debounceTimer);
        const search = $(this).val();
        debounceTimer = setTimeout(function() {
            fetch_data(1, $('#sort_by').val(), $('#sort_order').val(), search, getCurrentStatus());
        }, 300);
    });

    $(document).on('click', '#subscribed-table-container .sort-link', function(e) {
        e.preventDefault();
        fetch_data(1, $(this).data('sortby'), $(this).data('sortorder'), $('#search-input').val(), getCurrentStatus());
    });

    $(document).on('click', '#subscribed-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetch_data(page, $('#sort_by').val(), $('#sort_order').val(), $('#search-input').val(), getCurrentStatus());
    });

    setTimeout(function() {
        $('#success-alert, #error-alert').fadeOut('slow');
    }, 5000);
});
</script>
@stop