@extends('layouts.app')

@section('title', 'Client Report')
@section('plugins.Select2', true)

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
    .client-block .card-header { background-color: #6c757d; color: #fff; padding: 0; }
    .client-block .btn-link { color: #fff; text-decoration: none; font-size: 1.25rem; font-weight: 600; padding: 1rem 1.25rem; }
    .client-block .btn-link:hover { text-decoration: none; }
    .service-block { border: 1px solid #17a2b8; border-radius: .25rem; margin-bottom: 1.5rem; }
    .service-header { background-color: #17a2b8; color: #fff; padding: 1rem 1.25rem; font-size: 1.2rem; font-weight: 600; cursor: pointer; }
    .service-header:hover { color: #fff; background-color: #138496; }
    .job-block { border-top: 1px solid #dee2e6; }
    .job-header { background-color: #f8f9fa; padding: .75rem 1.25rem; font-weight: bold; cursor: pointer; }
     .job-header:hover { background-color: #e9ecef; }
    .task-list-item { display: flex; justify-content: space-between; align-items: center; padding: .75rem 1.25rem; border-top: 1px solid #e9ecef; }
    .task-details { flex-grow: 1; }
    .time-display { min-width: 100px; text-align: right; font-weight: bold; font-size: 1.1rem; }
    .collapse-icon { transition: transform 0.2s ease-in-out; }
    a[aria-expanded="true"] .collapse-icon, div[aria-expanded="true"] .collapse-icon { transform: rotate(180deg); }
    @media print {
        .client-block .card-header, .service-header { -webkit-print-color-adjust: exact; color-adjust: exact; }
        .job-header { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; color-adjust: exact; }
    }
</style>
@stop

@section('content')
<div class="card card-info card-outline">
    <div class="card-body">
        <!-- Filter and Search Row -->
        <div class="row mb-4 align-items-center d-print-none">
            <div class="col-md-3">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Client Name..." value="{{ $search ?? '' }}">
            </div>
            <div class="col-md-3">
                 <select id="status-filter" class="form-control" multiple="multiple" style="width: 100%;">
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                </select>
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
                            @foreach ($months as $num => $name)
                                <option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div id="custom-range-filters" class="row" style="display: none;">
                    <div class="col">
                        <input type="date" id="start-date-filter" class="form-control" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <div class="col">
                        <input type="date" id="end-date-filter" class="form-control" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                    </div>
                </div>
            </div>
             <div class="col-md-2 d-flex justify-content-end">
                 <div class="custom-control custom-switch mr-3 pt-1">
                    <input type="checkbox" class="custom-control-input" id="custom-range-switch">
                    <label class="custom-control-label" for="custom-range-switch">Custom</label>
                </div>
                <button class="btn btn-secondary" id="reset-filters">Reset</button>
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

    $('#status-filter').select2({
        placeholder: 'Filter by Status (All)',
        allowClear: true
    }).val(['ongoing', 'completed']).trigger('change');

    function fetchData() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            let data = {
                search: $('#search-input').val(),
                statuses: $('#status-filter').val(),
                use_custom_range: $('#custom-range-switch').is(':checked').toString()
            };
            
            if (data.use_custom_range === 'true') {
                data.start_date = $('#start-date-filter').val();
                data.end_date = $('#end-date-filter').val();
            } else {
                data.year = $('#year-filter').val();
                data.month = $('#month-filter').val();
            }
            
            $('#report-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');

            $.ajax({
                url: "{{ route('organization.reports.time') }}",
                data: data,
                success: (response) => {
                    $('#report-container').html(response);
                    $('#report-container').trigger('content-loaded');
                },
                error: () => $('#report-container').html('<p class="text-danger text-center">Failed to load data.</p>')
            });
        }, 500);
    }
    
    $('#custom-range-switch').on('change', function() {
        $('#dropdown-filters').toggle(!this.checked);
        $('#custom-range-filters').toggle(this.checked);
        fetchData();
    });

    $('#reset-filters').on('click', function() {
        $('#search-input').val('');
        $('#status-filter').val(['ongoing', 'completed']).trigger('change');
        $('#custom-range-switch').prop('checked', false).trigger('change');
        $('#year-filter').val('{{ $currentYear }}');
        $('#month-filter').val('{{ $currentMonth }}');
        fetchData();
    });

    $('#search-input, #start-date-filter, #end-date-filter, #year-filter, #month-filter, #status-filter').on('keyup change', fetchData);

    $(document).on('content-loaded', '#report-container', function() {
        function formatTime(totalSeconds) {
            if (isNaN(totalSeconds) || totalSeconds < 0) totalSeconds = 0;
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

        $('[data-toggle="collapse"]').on('click', function() {
            var target = $(this).attr('href') || $(this).data('target');
            $(target).collapse('toggle');
        });
    }).trigger('content-loaded');
    
    fetchData();
});
</script>
@stop