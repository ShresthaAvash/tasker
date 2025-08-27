@extends('layouts.app')

@section('title', 'My Reports')
@section('plugins.Select2', true)

@section('css')
    @parent 
    <style>
        .card-header a { text-decoration: none !important; display: block; }
        /* MODIFIED: Changed the service header background to primary blue */
        .report-header-service { background-color: #007bff; color: white; }
        .report-header-service a, .report-header-service .total-time-display { color: white !important; }
        .report-header-job { background-color: #e9ecef; color: #343a40; }
        .report-header-job .total-time-display { color: #343a40 !important; }
        .total-time-display { font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 1.1rem; }
        .collapse-icon { transition: transform 0.2s ease-in-out; }
        a[aria-expanded="false"] .collapse-icon { transform: rotate(-90deg); }
        .list-group-item strong { font-weight: 500; }
        .staff-breakdown { background-color: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef; }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>My Reports</h1>
        <button class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-print"></i> Print Report</button>
    </div>
@stop

@section('content')
{{-- MODIFIED: Changed the card theme to primary blue --}}
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-4 align-items-center bg-light p-3 rounded d-print-none">
            <div class="col-md-3">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Service, Job, or Task..." value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-3">
                <select id="status-filter" class="form-control" multiple="multiple"></select>
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
                                <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>{{ $name }}</option>
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

        <div id="client-report-table-container">
            @include('Client._report_table', ['groupedTasks' => $groupedTasks])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    $('#status-filter').select2({
        placeholder: 'Filter by Status',
        data: [
            { id: 'to_do', text: 'To Do' },
            { id: 'ongoing', text: 'Ongoing' },
            { id: 'completed', text: 'Completed' }
        ]
    }).val(@json($statuses)).trigger('change');

    function fetch_report_data() {
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
                url: "{{ route('client.reports.index') }}",
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

    // Initial state
    toggleDateFilters($('#custom-range-switch').is(':checked'));

    $('#search-input, #status-filter, #year-filter, #month-filter, #start-date-filter, #end-date-filter').on('keyup change', fetch_report_data);
});
</script>
@stop