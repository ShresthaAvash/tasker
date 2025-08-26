@extends('layouts.app')

@section('title', 'Staff Report')
@section('plugins.Select2', true)

@section('css')
    @parent 
    <style>
        .card-header a { text-decoration: none !important; display: block; }
<<<<<<< HEAD
        .report-header-staff { background-color: #6c757d; color: white; }
        .report-header-staff a, .report-header-staff .total-time-display { color: white !important; }
        /* THIS IS THE UPDATED COLOR */
        .report-header-service { background-color: #007afe; color: white; } 
=======
        .report-header-service { background-color: #17a2b8; color: white; }
>>>>>>> f93bfd0ed0f01c5965c1de5e5cf9cc0a8f0e656e
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
<div class="card card-primary card-outline">
    <div class="card-body">
        <div class="row mb-4 align-items-center bg-light p-3 rounded d-print-none">
            <div class="col-md-3">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Staff Name..." value="{{ $search ?? '' }}">
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
<<<<<<< HEAD
    const statuses = {!! json_encode($statuses) !!};

=======
    
    // --- THIS IS THE FIX: Added 'To Do' status to the filter options ---
>>>>>>> f93bfd0ed0f01c5965c1de5e5cf9cc0a8f0e656e
    $('#status-filter').select2({
        placeholder: 'Filter by Status (default all)',
        data: [
            { id: 'to_do', text: 'To Do' },
            { id: 'ongoing', text: 'Ongoing' },
            { id: 'completed', text: 'Completed' }
        ]
    }).val(@json($statuses)).trigger('change');

    function fetch_report_data() {
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
    $('#search-input, #status-filter, #year-filter, #month-filter, #start-date-filter, #end-date-filter').on('keyup change', fetch_report_data);

    $(document).on('click', '#staff-report-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetch_report_data(page);
    });
});
</script>
@stop