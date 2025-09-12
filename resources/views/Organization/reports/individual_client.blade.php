@extends('layouts.app')

@section('title', 'Client Report: ' . $client->name)
@section('plugins.Select2', true)

@section('content_header')
    <h1>Client Report: {{ $client->name }}</h1>
@stop

@section('css')
<style>
    /* Modern UI Styles to match dashboard */
    .stat-card { background-color: #fff; border-radius: .75rem; padding: 1.5rem; box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05); border: none; height: 100%; }
    .stat-title { color: #6c757d; font-size: 1rem; font-weight: 500; margin-bottom: 0.25rem; }
    .stat-number { font-size: 2.2rem; font-weight: 700; color: #212529; }
    .table-responsive { animation: fadeIn 0.5s ease-out; }
    .service-row { background-color: #f8f9fa; font-weight: bold; cursor: pointer; transition: background-color 0.2s ease-in-out; }
    .service-row:hover { background-color: #e9ecef; }
    .task-row { display: none; background-color: #fff; }
    .task-row td { border-top: 1px solid #f1f1f1; }
    .task-row .task-name-cell { padding-left: 2.5rem !important; }
    .collapse-icon { transition: transform 0.3s ease; }
    .is-expanded .collapse-icon { transform: rotate(-180deg); }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Report Filters</h3>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-2"><input type="text" id="search-input" class="form-control" placeholder="Search Tasks or Services..."></div>
                    <div class="col-md-2"><select id="service-filter" class="form-control" multiple="multiple"></select></div>
                    <div class="col-md-2"><select id="staff-filter" class="form-control" multiple="multiple"></select></div>
                    <div class="col-md-2"><select id="status-filter" class="form-control" multiple="multiple"></select></div>
                    <div class="col-md-3">
                        <div id="dropdown-filters" class="row">
                            <div class="col"><select id="year-filter" class="form-control">@foreach($years as $year)<option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>@endforeach</select></div>
                            <div class="col"><select id="month-filter" class="form-control">@foreach($months as $num => $name)<option value="{{ $num }}" {{ (string)$num === (string)$currentMonth ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></div>
                        </div>
                    </div>
                    <div class="col-md-1 d-flex justify-content-end"><button class="btn btn-secondary" id="reset-filters">Reset</button></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="report-table-container">
    {{-- --- THIS IS THE FIX --- --}}
    @include('Organization.reports._individual_client_report_table', compact('groupedTasks'))
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    function initializeSelect2() {
        $('#service-filter').select2({ placeholder: 'Filter by Service', data: {!! json_encode($services->map(fn($s) => ['id' => $s->id, 'text' => $s->name])) !!} });
        $('#staff-filter').select2({ placeholder: 'Filter by Staff', data: {!! json_encode($staff->map(fn($s) => ['id' => $s->id, 'text' => $s->name])) !!} });
        $('#status-filter').select2({
            placeholder: 'Filter by Status',
            data: [ { id: 'to_do', text: 'To Do' }, { id: 'ongoing', text: 'Ongoing' }, { id: 'completed', text: 'Completed' } ]
        });
    }
    initializeSelect2();

    function fetch_report_data() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $('#report-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
            $.ajax({
                url: window.location.pathname,
                data: {
                    search: $('#search-input').val(),
                    services: $('#service-filter').val(),
                    staff: $('#staff-filter').val(),
                    statuses: $('#status-filter').val(),
                    year: $('#year-filter').val(),
                    month: $('#month-filter').val(),
                },
                success: (response) => $('#report-table-container').html(response),
                error: () => $('#report-table-container').html('<p class="text-danger text-center">Failed to load data.</p>')
            });
        }, 500);
    }
    
    $('#search-input, #service-filter, #staff-filter, #status-filter, #year-filter, #month-filter').on('keyup change', fetch_report_data);

    $('#reset-filters').on('click', function() {
        $('#search-input').val('');
        $('#service-filter, #staff-filter, #status-filter').val(null).trigger('change');
        const now = new Date();
        $('#year-filter').val(now.getFullYear());
        $('#month-filter').val('all');
        fetch_report_data();
    });

    $(document).on('click', '.service-row', function() {
        const serviceId = $(this).data('service-id');
        $(this).toggleClass('is-expanded');
        $('.task-for-service-' + serviceId).fadeToggle(200);
    });

    $(document).on('keyup', '#search-input', function() {
        const searchTerm = $(this).val().toLowerCase();
        if (searchTerm.length === 0) {
            $('.service-row').removeClass('is-expanded');
            $('.task-row').hide();
            return;
        }

        $('.service-row').each(function() {
            let serviceHasVisibleTasks = false;
            const serviceId = $(this).data('service-id');
            const taskRows = $('.task-for-service-' + serviceId);

            taskRows.each(function() {
                const taskName = $(this).find('.task-name-cell').text().toLowerCase();
                if (taskName.includes(searchTerm)) {
                    $(this).show();
                    serviceHasVisibleTasks = true;
                } else {
                    $(this).hide();
                }
            });

            if (serviceHasVisibleTasks) {
                $(this).addClass('is-expanded').show();
            } else {
                $(this).removeClass('is-expanded').hide();
            }
        });
    });
});
</script>
@stop