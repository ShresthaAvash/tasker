@extends('layouts.app')

@section('title', 'Staff Report')
@section('plugins.Select2', true)

@section('css')
    @parent 
    {{-- Re-using the same styles for consistency --}}
    <style>
        .filter-card {
            background-color: #fff;
            border-radius: .375rem;
            box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .report-container {
            padding: 0;
        }
        .report-group {
            background-color: #fff;
            border-radius: .75rem;
            margin-bottom: 1.5rem;
            overflow: hidden;
            box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);
        }
        .report-header {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            text-decoration: none !important;
            color: inherit;
            transition: background-color 0.2s ease-in-out;
        }
        .report-header:hover {
            background-color: #f1f1f1;
        }
        .report-header.staff-header { 
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
        .report-header.staff-header:hover {
            background-color: #e9ecef;
        }
        .report-header.service-header { 
            background-color: #007bff;
            color: white; 
        }
        .report-header.service-header:hover {
            background-color: #0069d9;
        }
        .report-header.job-header { 
            background-color: #fff;
            border-top: 1px solid #e9ecef; 
            border-bottom: 1px solid #e9ecef; 
        }
        .report-header.job-header:hover {
            background-color: #f8f9fa;
        }
        
        .report-title { font-weight: 600; font-size: 1.1rem; margin-bottom: 0; }
        .report-time { font-size: 0.9rem; font-weight: 500; }
        .report-header.service-header .report-time { color: rgba(255,255,255,0.85); }

        .task-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .task-item:last-child { border-bottom: none; }
        .task-details { display: flex; align-items: center; gap: .75rem; }
        .task-details i { color: #6c757d; }
        .task-name { font-weight: 500; }
        
        .status-pill {
            padding: .3em .8em;
            font-size: .75em;
            font-weight: 700;
            border-radius: 50px;
            white-space: nowrap;
        }
        .status-to_do, .status-to-do { background-color: #f8d7da; color: #721c24; }
        .status-ongoing, .status-in-progress { background-color: #d1ecf1; color: #0c5460; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-not-started-yet { background-color: #e9ecef; color: #495057; }

        .collapse-icon { 
            transition: transform 0.3s ease; 
        }
        a[aria-expanded="false"] .collapse-icon { 
            transform: rotate(-90deg); 
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Staff Report</h1>
        <button class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-print"></i> Print Report</button>
    </div>
@stop

@section('content')
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-4 align-items-center bg-light p-3 rounded d-print-none">
            <div class="col-md-3">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Staff, Client, etc..." value="{{ $search ?? '' }}">
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

        <div id="staff-report-table-container" class="report-container">
            @include('Organization.reports._staff_report_table', ['reportData' => $reportData])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    $('#status-filter').select2({
        placeholder: 'Filter by Status (default all)',
        width: '100%',
        data: [
            { id: 'to_do', text: 'To Do' },
            { id: 'ongoing', text: 'Ongoing' },
            { id: 'completed', text: 'Completed' }
        ]
    }).val({!! json_encode($statuses) !!}).trigger('change');

    function fetch_report_data(page = 1) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $('#staff-report-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
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
                url: "{{ route('organization.reports.staff') }}",
                data: data,
                success: (response) => $('#staff-report-table-container').html(response),
                error: () => $('#staff-report-table-container').html('<p class="text-danger text-center">Failed to load data.</p>')
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

    $(document).on('click', '#staff-report-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetch_report_data(page);
    });
});
</script>
@stop