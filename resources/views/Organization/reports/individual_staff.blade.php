@extends('layouts.app')

@section('title', 'Staff Report: ' . $staff->name)
@section('plugins.Select2', true)

@section('content_header')
    <h1>Staff Report: {{ $staff->name }}</h1>
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
                    <div class="col-md-3">
                        <input type="text" id="search-input" class="form-control" placeholder="Search by Task, Client, or Service..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select id="client-filter" class="form-control" multiple="multiple"></select>
                    </div>
                    <div class="col-md-2">
                        <select id="service-filter" class="form-control" multiple="multiple"></select>
                    </div>
                    <div class="col-md-2">
                        <select id="status-filter" class="form-control" multiple="multiple"></select>
                    </div>
                    <div class="col-md-3">
                        <div id="dropdown-filters" class="row">
                            <div class="col"><select id="year-filter" class="form-control">@foreach($years as $year)<option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>@endforeach</select></div>
                            <div class="col"><select id="month-filter" class="form-control">@foreach($months as $num => $name)<option value="{{ $num }}" {{ (string)$num === (string)$currentMonth ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="report-table-container">
    @include('Organization.reports._individual_staff_report_table', compact('groupedTasks'))
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    // Initialize Select2 Filters
    $('#client-filter').select2({ placeholder: 'Filter by Client', data: {!! json_encode($clients->map(fn($c) => ['id' => $c->id, 'text' => $c->name])) !!} });
    $('#service-filter').select2({ placeholder: 'Filter by Service', data: {!! json_encode($services->map(fn($s) => ['id' => $s->id, 'text' => $s->name])) !!} });
    $('#status-filter').select2({
        placeholder: 'Filter by Status',
        data: [ { id: 'to_do', text: 'To Do' }, { id: 'ongoing', text: 'Ongoing' }, { id: 'completed', text: 'Completed' } ]
    });

    function fetch_report_data(page = 1) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $('#report-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
            
            let data = {
                page: page,
                search: $('#search-input').val(),
                clients: $('#client-filter').val(),
                services: $('#service-filter').val(),
                statuses: $('#status-filter').val(),
                year: $('#year-filter').val(),
                month: $('#month-filter').val(),
            };

            $.ajax({
                url: window.location.pathname,
                data: data,
                success: (response) => $('#report-table-container').html(response),
                error: () => $('#report-table-container').html('<p class="text-danger text-center">Failed to load report data.</p>')
            });
        }, 500);
    }

    // Event listeners for filters
    $('#search-input, #client-filter, #service-filter, #status-filter, #year-filter, #month-filter').on('keyup change', function() {
        fetch_report_data(1);
    });

    $(document).on('click', '.service-row', function() {
        const serviceId = $(this).data('service-id');
        $(this).toggleClass('is-expanded');
        $('.task-for-service-' + serviceId).fadeToggle(200);
    });

    $(document).on('keyup', '#search-input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.service-row').each(function() {
            let serviceHasVisibleTasks = false;
            const serviceId = $(this).data('service-id');
            const taskRows = $('.task-for-service-' + serviceId);
            const serviceName = $(this).find('td:first').text().toLowerCase();

            taskRows.each(function() {
                const taskName = $(this).find('.task-name-cell').text().toLowerCase();
                if (taskName.includes(searchTerm) || serviceName.includes(searchTerm)) {
                    $(this).show();
                    serviceHasVisibleTasks = true;
                } else {
                    $(this).hide();
                }
            });

            if (serviceHasVisibleTasks) {
                $(this).addClass('is-expanded').show();
                taskRows.show(); // Ensure tasks are visible if parent is
            } else {
                $(this).removeClass('is-expanded').hide();
            }
        });
    });
});
</script>
@stop