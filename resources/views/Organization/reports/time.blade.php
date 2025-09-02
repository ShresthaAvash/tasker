@extends('layouts.app')

@section('title', 'Client Report')
@section('plugins.Select2', true)

@section('css')
    @parent 
    <style>
        .filter-card {
            background-color: #fff;
            border-radius: .75rem;
            box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .report-container {
            padding: 0;
        }
        .client-block {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: .75rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
        }
        .block-header {
            padding: 1rem 1.25rem;
            border-radius: .5rem;
            margin-bottom: 1rem;
        }
        .client-header {
            background-color: #f8f9fa;
            color: #343a40;
            border: 1px solid #dee2e6;
        }
        .service-header {
            background-color: #007bff;
            color: white;
        }
        .job-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            color: #343a40;
            margin-bottom: 0.5rem;
            padding: .75rem 1rem;
        }
        .block-title {
            font-weight: 600;
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }
        .block-title i {
            margin-right: .75rem;
        }
        .block-status {
            font-weight: 500;
            font-size: 0.9rem;
        }
        .task-list {
            padding-left: 1.5rem;
            border-left: 2px solid #e9ecef;
            margin-left: .5rem;
        }
        .task-item {
            background-color: #fff;
            padding: 1rem 0;
            margin-bottom: 0;
            border-bottom: 1px solid #e9ecef;
        }
        .task-item:last-child {
            border-bottom: none;
            padding-bottom: 0.5rem;
        }
        .task-main-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .task-details {
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .task-details i {
            color: #6c757d;
        }
        .task-name {
            font-weight: 500;
        }
        .task-staff {
            font-size: 0.85rem;
            color: #6c757d;
            cursor: pointer;
            text-decoration: none;
            border-bottom: 1px dashed #6c757d;
        }
        .task-staff:hover {
            color: #007bff;
            border-color: #007bff;
        }
        .status-pill {
            padding: .3em .8em;
            font-size: .75em;
            font-weight: 700;
            border-radius: 50px;
            white-space: nowrap;
        }
        .status-not-started-yet { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
        .status-to-do { background-color: #ffe5e5; color: #c81e1e; border: 1px solid #f5c6cb; }
        .status-in-progress { background-color: #cce5ff; color: #004085; border: 1px solid #b8daff; }
        .status-completed { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        .staff-breakdown { background-color: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef; }
        .collapse-icon { transition: transform 0.3s ease; }
        a[aria-expanded="false"] .collapse-icon { transform: rotate(-90deg); }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Client Report</h1>
        <button class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-print"></i> Print Report</button>
    </div>
@stop

@section('content')
<div class="filter-card d-print-none">
    <div class="row align-items-center">
        <div class="col-md-3">
            <input type="text" id="search-input" class="form-control" placeholder="Search by Client, Service, Job..." value="{{ $search ?? '' }}">
        </div>
        <div class="col-md-3">
            <select id="status-filter" multiple="multiple"></select>
        </div>
        <div class="col-md-4">
            <div id="dropdown-filters" class="row">
                <div class="col">
                    <select id="year-filter" class="form-control">
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <select id="month-filter" class="form-control">
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}" {{ (string)$num === (string)$currentMonth ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div id="custom-range-filters" class="row" style="display: none;">
                <div class="col">
                    <input type="date" id="start-date-filter" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col">
                    <input type="date" id="end-date-filter" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>
            </div>
        </div>
        <div class="col-md-2 d-flex justify-content-end align-items-center">
             <div class="custom-control custom-switch mr-3 pt-1">
                <input type="checkbox" class="custom-control-input" id="custom-range-switch" {{ $use_custom_range ? 'checked' : '' }}>
                <label class="custom-control-label" for="custom-range-switch">Custom</label>
            </div>
            <button class="btn btn-secondary" id="reset-filters">Reset</button>
        </div>
    </div>
</div>

<div id="client-report-table-container" class="report-container">
    @include('Organization.reports._client_report_table', ['groupedTasks' => $groupedTasks])
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    $('#status-filter').select2({
        placeholder: 'Filter by Status: All',
        width: '100%',
        data: [
            { id: 'to_do', text: 'To Do' },
            { id: 'ongoing', text: 'In Progress' },
            { id: 'completed', text: 'Completed' }
        ]
    }).val({!! json_encode($statuses) !!}).trigger('change');

    function fetch_report_data(page = 1) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $('#client-report-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
            let data = {
                search: $('#search-input').val(),
                statuses: $('#status-filter').val(),
                use_custom_range: $('#custom-range-switch').is(':checked').toString(),
                start_date: $('#start-date-filter').val(),
                end_date: $('#end-date-filter').val(),
                year: $('#year-filter').val(),
                month: $('#month-filter').val()
            };
            $.ajax({
                url: "{{ route('organization.reports.time') }}",
                data: data,
                success: (response) => $('#client-report-table-container').html(response),
                error: () => $('#client-report-table-container').html('<p class="text-danger text-center">Failed to load data.</p>')
            });
        }, 500);
    }

    function toggleDateFilters(useCustom) {
        $('#dropdown-filters').toggle(!useCustom);
        $('#custom-range-filters').toggle(useCustom);
    }

    $('#custom-range-switch').on('change', function() {
        toggleDateFilters(this.checked);
        fetch_report_data();
    });
    
    $('#reset-filters').on('click', function() {
        const today = new Date();
        $('#search-input').val('');
        $('#status-filter').val(null).trigger('change.select2');
        $('#custom-range-switch').prop('checked', false);
        $('#year-filter').val(today.getFullYear());
        $('#month-filter').val(today.getMonth() + 1);
        toggleDateFilters(false);
        fetch_report_data();
    });

    toggleDateFilters($('#custom-range-switch').is(':checked'));
    $('#search-input, #status-filter, #year-filter, #month-filter, #start-date-filter, #end-date-filter').on('keyup change', function() {
        fetch_report_data(1);
    });

    $(document).on('click', '#client-report-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetch_report_data(page);
    });
});
</script>
@stop