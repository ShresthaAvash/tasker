@extends('layouts.app')

@section('title', 'Client Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center d-print-none">
        <h1>Client Report</h1>
        <button onclick="window.print();" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
@stop

@section('css')
<style>
    .client-block .card-header {
        background-color: #6c757d;
        color: #fff;
        padding: 0;
    }
    .client-block .btn-link {
        color: #fff;
        text-decoration: none;
        font-size: 1.25rem;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .client-block .btn-link:hover {
        text-decoration: none;
    }
    .service-block {
        border: 1px solid #17a2b8;
        border-radius: .25rem;
        margin-bottom: 1.5rem;
    }
    .service-header {
        background-color: #17a2b8;
        color: #fff;
        padding: 1rem 1.25rem;
        font-size: 1.25rem;
        font-weight: 600;
    }
    /* --- THIS IS THE FIX: Keep text white on hover --- */
    .service-header:hover {
        color: #fff;
    }
    .job-block {
        border-top: 1px solid #dee2e6;
    }
    .job-header {
        background-color: #f8f9fa;
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
    .task-details {
        flex-grow: 1;
    }
    .staff-list .badge {
        font-size: 90%;
        margin-right: 5px;
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
    @media print {
        .client-header, .service-header {
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
                <input type="text" id="search-input" class="form-control" placeholder="Search by Client Name..." value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-5">
                <div id="custom-range-filters" class="row" style="display: {{ $active_period === 'custom' ? '' : 'none' }};">
                    <div class="col"><input type="date" id="start-date-filter" class="form-control" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"></div>
                    <div class="col"><input type="date" id="end-date-filter" class="form-control" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}"></div>
                </div>
            </div>
        </div>

        <div id="report-container">
            @include('Organization.reports._client_report_table')
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
                url: "{{ route('organization.reports.time') }}",
                data: { period, search, start_date: startDate, end_date: endDate },
                success: (data) => {
                    $('#report-container').html(data);
                    // Re-run total calculations after content is loaded
                    $('#report-container').trigger('content-loaded');
                },
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

    $(document).on('content-loaded', '#report-container', function() {
        function formatTime(totalSeconds) {
            const hours = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
            const minutes = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
            const seconds = (totalSeconds % 60).toString().padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        }
        $('.client-block').each(function() {
            let clientTotal = 0;
            $(this).find('.service-block').each(function() {
                let serviceTotal = 0;
                $(this).find('.job-block').each(function() {
                    let jobTotal = 0;
                    $(this).find('.task-list-item').each(function() {
                        jobTotal += parseInt($(this).data('task-time')) || 0;
                    });
                    $(this).find('.job-total-time').text(formatTime(jobTotal));
                    serviceTotal += jobTotal;
                });
                $(this).find('.service-total-time').text(formatTime(serviceTotal));
                clientTotal += serviceTotal;
            });
            $(this).find('.client-total-time').text(formatTime(clientTotal));
        });
    }).trigger('content-loaded');
});
</script>
@stop