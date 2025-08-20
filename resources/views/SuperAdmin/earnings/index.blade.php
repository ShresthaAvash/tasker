@extends('layouts.app')

@section('title', 'Earnings Report')

@section('content_header')
    <h1>Earnings Report</h1>
@stop

@section('content')
{{-- Earnings Info Boxes --}}
<div class="row">
    {{-- MODIFIED: Grid classes changed to col-lg-3 for a four-column layout --}}
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>${{ number_format($monthlyEarnings, 2) }}</h3>
                <p>Monthly Revenue</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>${{ number_format($yearlyEarnings, 2) }}</h3>
                <p>Yearly Revenue</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-purple">
            <div class="inner">
                <h3>${{ number_format($totalRevenue, 2) }}</h3>
                <p>Total Revenue</p>
            </div>
            <div class="icon"><i class="fas fa-coins"></i></div>
        </div>
    </div>
    {{-- NEW: Fourth info box for total subscriptions --}}
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalSubscriptionCount }}</h3>
                <p>Total Subscriptions</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
</div>

<div class="card card-info card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
        {{-- --- THIS IS THE MODIFIED TAB STRUCTURE --- --}}
        <ul class="nav nav-tabs" id="earnings-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="total-tab" data-type="total" data-toggle="pill" href="#table-content" role="tab">Total Subscriptions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="monthly-tab" data-type="monthly" data-toggle="pill" href="#table-content" role="tab">Monthly Subscriptions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="yearly-tab" data-type="annually" data-toggle="pill" href="#table-content" role="tab">Yearly Subscriptions</a>
            </li>
        </ul>
    </div>
    <div class="card-header">
        <h3 class="card-title" id="card-title">{{ $title }}</h3>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <div class="input-group">
                <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by organization name or email..." value="{{ request('search') }}">
            </div>
        </div>

        <div id="earnings-table-container">
            @include('SuperAdmin.earnings._table', ['organizations' => $organizations])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    function fetch_data(page, search, type) {
        $('#earnings-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('superadmin.earnings') }}",
            data: { page: page, search: search, type: type },
            success: function(data) {
                $('#earnings-table-container').html(data);
            },
            error: function() {
                alert('Could not load data. Please refresh the page.');
                $('#earnings-table-container').html('<p class="text-danger text-center">Failed to load data.</p>');
            }
        });
    }
    
    // --- MODIFIED --- Default to 'total'
    function getCurrentType() {
        return $('#earnings-tabs .nav-link.active').data('type') || 'total';
    }

    $('#earnings-tabs a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        const type = $(e.target).data('type');
        
        // --- MODIFIED --- Handle title for the new tab
        let title;
        if (type === 'monthly') {
            title = 'Monthly Subscriptions';
        } else if (type === 'annually') {
            title = 'Yearly Subscriptions';
        } else {
            title = 'All Active Subscriptions';
        }
        
        $('#card-title').text(title);
        fetch_data(1, $('#search-input').val(), type);
    });

    $('#search-input').on('keyup', function() {
        clearTimeout(debounceTimer);
        const search = $(this).val();
        debounceTimer = setTimeout(function() {
            fetch_data(1, search, getCurrentType());
        }, 300);
    });

    $(document).on('click', '#earnings-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetch_data(page, $('#search-input').val(), getCurrentType());
    });
});
</script>
@stop