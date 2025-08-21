@extends('layouts.app')

@section('title', 'Staff Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center d-print-none">
        <h1>Staff Report</h1>
        <button onclick="window.print();" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
@stop

@section('css')
<style>
    /* --- THIS IS THE MODIFIED STYLE --- */
    .staff-block .card-header {
        background-color: #6c757d; /* Dark Grey Header */
        color: #fff;
        padding: 0;
    }
    .staff-block .btn-link {
        color: #fff;
        text-decoration: none !important;
        font-size: 1.25rem;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .staff-block .btn-link:hover {
        text-decoration: none;
    }
    .service-block {
        border: 1px solid #17a2b8;
        border-radius: .25rem;
        margin-bottom: 1.5rem;
    }
    .service-header {
        background-color: #17a2b8; /* Teal Header */
        color: #fff;
        padding: 1rem 1.25rem;
        font-size: 1.2rem;
        font-weight: 600;
    }
    .job-block {
        border-top: 1px solid #dee2e6;
    }
    .job-header {
        background-color: #f8f9fa; /* Light Grey Header */
        padding: .75rem 1.25rem;
        font-weight: bold;
    }
    .task-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .75rem 1.25rem;
        border-top: 1px solid #e9ecef;
    }
    .time-display {
        min-width: 100px;
        text-align: right;
        font-weight: bold;
        font-size: 1.1rem;
    }
    .collapse-icon {
        transition: transform 0.2s ease-in-out;
    }
    a[aria-expanded="true"] .collapse-icon {
        transform: rotate(180deg);
    }
    @media  print {
        .staff-header, .service-header {
            -webkit-print-color-adjust: exact; 
            color-adjust: exact;
        }
        .job-header {
            background-color: #f8f9fa !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }
    }
</style>
@stop

@section('content')
<div class="card card-info card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0 d-print-none">
        <ul class="nav nav-tabs" id="report-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link {{ $active_period == 'day' ? 'active' : '' }}" data-period="day" href="#">Today</a></li>
            <li class="nav-item"><a class="nav-link {{ $active_period == 'week' ? 'active' : '' }}" data-period="week" href="#">This Week</a></li>
            <li class="nav-item"><a class="nav-link {{ $active_period == 'month' ? 'active' : '' }}" data-period="month" href="#">This Month</a></li>
            <li class="nav-item"><a class="nav-link {{ $active_period == 'year' ? 'active' : '' }}" data-period="year" href="#">This Year</a></li>
            <li class="nav-item"><a class="nav-link {{ $active_period == 'all' ? 'active' : '' }}" data-period="all" href="#">All Time</a></li>
            <li class="nav-item"><a class="nav-link {{ $active_period == 'custom' ? 'active' : '' }}" data-period="custom" href="#">Custom</a></li>
        </ul>
    </div>
    <div class="card-body">
        <div class="row mb-3 align-items-center d-print-none">
            <div class="col-md-5">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Staff Name..." value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-5">
                <div id="custom-range-filters" class="row" style="display: {{ $active_period === 'custom' ? '' : 'none' }};">
                    <div class="col"><input type="date" id="start-date-filter" class="form-control" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"></div>
                    <div class="col"><input type="date" id="end-date-filter" class="form-control" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}"></div>
                </div>
            </div>
        </div>

        <div id="report-container">
            @include('Organization.reports._staff_report_table')
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    function fetchData() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const period = $('#report-tabs .nav-link.active').data('period') || 'month';
            const search = $('#search-input').val();
            const startDate = $('#start-date-filter').val();
            const endDate = $('#end-date-filter').val();
            
            $('#report-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');

            $.ajax({
                url: "{{ route('organization.reports.staff') }}",
                data: { period, search, start_date: startDate, end_date: endDate },
                success: (data) => $('#report-container').html(data),
                error: () => $('#report-container').html('<p class="text-danger text-center">Failed to load data.</p>')
            });
        }, 300);
    }

    $('#report-tabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
        const period = $(this).data('period');
        $('#custom-range-filters').toggle(period === 'custom');
        if (period !== 'custom') {
            fetchData();
        }
    });

    $('#search-input, #start-date-filter, #end-date-filter').on('keyup change', fetchData);
});
</script>
@stop