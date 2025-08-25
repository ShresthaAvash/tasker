@extends('layouts.app')

@section('title', 'Staff Report')
@section('plugins.Select2', true)

@section('css')
    @parent
    <style>
        .card-header a { text-decoration: none !important; display: block; }
        .report-header-staff { background-color: #6c757d; color: white; }
        .report-header-staff a, .report-header-staff .total-time-display { color: white !important; }
        .report-header-service { background-color: #17a2b8; color: white; }
        .report-header-service a, .report-header-service .total-time-display { color: white !important; }
        .report-header-job { background-color: #e9ecef; color: #343a40; }
        .report-header-job .total-time-display { color: #343a40 !important; }
        .total-time-display { font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 1.1rem; }
        .collapse-icon { transition: transform 0.2s ease-in-out; }
        a[aria-expanded="false"] .collapse-icon { transform: rotate(-90deg); }
        .list-group-item strong { font-weight: 500; }
    </style>
@stop

@section('content_header')
     <div class="d-flex justify-content-between align-items-center">
        <h1>Staff Report</h1>
        <button class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-print"></i> Print Report</button>
    </div>
@stop

@section('content')
<div class="card card-info card-outline">
    <div class="card-body">
        <div class="row mb-4 align-items-center bg-light p-3 rounded d-print-none">
            <div class="col-md-3"><input type="text" id="search-input" class="form-control" placeholder="Search by Staff Name..." value="{{ $search ?? '' }}"></div>
            <div class="col-md-3"><select id="status-filter" class="form-control" multiple="multiple"></select></div>
            <div class="col-md-4">
                <div id="dropdown-filters" class="row">
                    <div class="col"><select id="year-filter" class="form-control">@foreach($years as $year)<option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>@endforeach</select></div>
                    <div class="col"><select id="month-filter" class="form-control">@foreach($months as $num => $name)<option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></div>
                </div>
                <div id="custom-range-filters" class="row" style="display: none;">
                    <div class="col"><input type="date" id="start-date-filter" class="form-control" value="{{ $startDate->format('Y-m-d') }}"></div>
                    <div class="col"><input type="date" id="end-date-filter" class="form-control" value="{{ $endDate->format('Y-m-d') }}"></div>
                </div>
            </div>
            <div class="col-md-2 d-flex justify-content-end align-items-center">
                 <div class="custom-control custom-switch mr-3 pt-1"><input type="checkbox" class="custom-control-input" id="custom-range-switch" {{ $use_custom_range ? 'checked' : '' }}><label class="custom-control-label" for="custom-range-switch">Custom</label></div>
                <button class="btn btn-secondary" id="reset-filters">Reset</button>
            </div>
        </div>

        <div id="staff-report-table-container">
            @include('Organization.reports._staff_report_table', ['reportData' => $reportData])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;
    const statuses = @json($statuses);

    $('#status-filter').select2({
        placeholder: 'Filter by Status (default all)',
        data: [ { id: 'ongoing', text: 'Ongoing' }, { id: 'completed', text: 'Completed' } ]
    }).val(statuses).trigger('change');

    function fetch_report_data() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $('#staff-report-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
            let data = {
                search: $('#search-input').val(), statuses: $('#status-filter').val(),
                use_custom_range: $('#custom-range-switch').is(':checked').toString(),
                start_date: $('#start-date-filter').val(), end_date: $('#end-date-filter').val(),
                year: $('#year-filter').val(), month: $('#month-filter').val()
            };
            $.ajax({
                url: "{{ route('organization.reports.staff') }}", data: data,
                success: (response) => $('#staff-report-table-container').html(response),
                error: () => $('#staff-report-table-container').html('<p class="text-danger text-center">Failed to load data.</p>')
            });
        }, 500);
    }
    
    $('#custom-range-switch').on('change', function() {
        $('#dropdown-filters').toggle(!this.checked);
        $('#custom-range-filters').toggle(this.checked);
        fetch_report_data();
    }).trigger('change');
    
    $('#reset-filters').on('click', function() {
        const today = new Date();
        $('#search-input').val('');
        $('#status-filter').val(null).trigger('change');
        $('#custom-range-switch').prop('checked', false).trigger('change');
        $('#year-filter').val(today.getFullYear());
        $('#month-filter').val(today.getMonth() + 1).trigger('change');
    });

    $('#search-input, #status-filter, #year-filter, #month-filter, #start-date-filter, #end-date-filter').on('keyup change', fetch_report_data);
});
</script>
@stop