@extends('layouts.app')

@section('title', 'Client Report: ' . $client->name)
@section('plugins.Select2', true)

@section('content_header')
    <h1>Client Report: <span class="text-muted">{{ $client->name }}</span></h1>
@stop

@section('css')
<style>
    .content-wrapper { background-color: #f4f6f9; }
    .filter-card {
        background-color: #ffffff;
        border: 1px solid #e3e6f0;
        border-radius: .5rem;
        padding: 1rem 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
    }
    .stat-card {
        background-color: #fff;
        border-radius: .5rem;
        padding: 1.25rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        border: 1px solid #e3e6f0;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .stat-title { color: #6c757d; font-size: 0.9rem; font-weight: 500; margin-bottom: 0.25rem; }
    .stat-number { font-size: 2rem; font-weight: 700; color: #212529; }
    .service-block {
        background-color: #fff;
        border-radius: .5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.04);
        border: 1px solid #e3e6f0;
    }
    .service-header {
        padding: 0.75rem 1.25rem;
        border-bottom: 1px solid #e3e6f0;
        cursor: pointer;
    }
    .service-title { font-weight: 600; font-size: 1.1rem; }
    .service-time { font-size: 0.9rem; color: #6c757d; font-weight: 500; }
    .task-table { margin-bottom: 0; }
    .task-table th { background-color: #f8f9fa; border-top: none !important; }
    .task-table td { vertical-align: middle; }
    .task-name-cell { font-weight: 500; }
    .staff-cell, .date-cell { font-size: 0.9rem; color: #6c757d; }
    .collapse-icon { transition: transform 0.3s ease; }
    .service-header[aria-expanded="false"] .collapse-icon { transform: rotate(-90deg); }

    /* Custom styles for the status dropdown to make it less prominent */
    .service-status-select {
        background-color: #f8f9fa;
        border-color: #ced4da;
        font-weight: 500;
    }
    .status-select-wrapper .select2-container .select2-selection--single {
        height: calc(1.8125rem + 2px) !important;
        padding: .25rem .5rem;
        font-size: .875rem;
    }
</style>
@stop

@section('content')
<div id="status-update-feedback" class="alert" style="position: fixed; top: 80px; right: 20px; z-index: 1050; display: none;"></div>

{{-- Filter Card --}}
<div class="filter-card d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col-md-2"><input type="text" id="search-input" class="form-control" placeholder="Search tasks..."></div>
        <div class="col-md-2"><select id="service-filter" class="form-control" multiple></select></div>
        <div class="col-md-2"><select id="staff-filter" class="form-control" multiple></select></div>
        <div class="col-md-2"><select id="status-filter" class="form-control" multiple></select></div>
        <div class="col-md-4">
            <div class="row">
                <div class="col"><select id="year-filter" class="form-control">@foreach($years as $year)<option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>@endforeach</select></div>
                <div class="col"><select id="month-filter" class="form-control">@foreach($months as $num => $name)<option value="{{ $num }}" {{ (string)$num === (string)$currentMonth ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></div>
            </div>
        </div>
    </div>
</div>

<div id="report-container">
    {{-- Content will be loaded via AJAX --}}
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    function initializeSelect2() {
        $('#service-filter').select2({ placeholder: 'Filter by Service', data: {!! json_encode($services->map(fn($s) => ['id' => $s->id, 'text' => $s->name])) !!}, allowClear: true });
        $('#staff-filter').select2({ placeholder: 'Filter by Staff', data: {!! json_encode($staff->map(fn($s) => ['id' => $s->id, 'text' => $s->name])) !!}, allowClear: true });
        $('#status-filter').select2({
            placeholder: 'Filter by Status', allowClear: true,
            data: [ { id: 'to_do', text: 'To Do' }, { id: 'ongoing', text: 'Ongoing' }, { id: 'completed', text: 'Completed' } ]
        });
    }
    initializeSelect2();

    function fetch_report_data() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $('#report-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
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
                success: (response) => $('#report-container').html(response),
                error: () => $('#report-container').html('<p class="text-danger text-center">Failed to load data.</p>')
            });
        }, 500);
    }
    
    fetch_report_data();

    $('#search-input, #service-filter, #staff-filter, #status-filter, #year-filter, #month-filter').on('keyup change', fetch_report_data);

    // --- SERVICE STATUS UPDATE ---
    $(document).on('change', '.service-status-select', function() {
        const select = $(this);
        const serviceId = select.data('service-id');
        const newStatus = select.val();
        
        $.ajax({
            url: `/organization/reports/client/{{ $client->id }}/service/${serviceId}/status`,
            method: 'PATCH',
            data: { 
                status: newStatus,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                const feedback = $('#status-update-feedback');
                feedback.removeClass('alert-danger').addClass('alert-success').text(response.success).fadeIn();
                setTimeout(() => feedback.fadeOut(), 3000);
            },
            error: function(xhr) {
                const feedback = $('#status-update-feedback');
                const errorMsg = xhr.responseJSON ? xhr.responseJSON.error : 'An error occurred.';
                feedback.removeClass('alert-success').addClass('alert-danger').text(errorMsg).fadeIn();
                setTimeout(() => feedback.fadeOut(), 5000);
            }
        });
    });
});
</script>
@stop