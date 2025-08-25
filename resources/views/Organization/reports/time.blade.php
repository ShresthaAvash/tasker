@extends('layouts.app')

@section('title', 'Client Report')
@section('plugins.Select2', true)

@section('css')
    @parent {{-- This inherits the base styles from the layout --}}
    <style>
        .total-time-badge {
            font-size: 1.1em;
            font-weight: bold;
        }
        .accordion .card-header {
            cursor: pointer;
            background-color: #f8f9fa;
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Client Report</h1>
        <button class="btn btn-primary" onclick="window.print();"><i class="fas fa-print"></i> Print Report</button>
    </div>
@stop

@section('content')
<div class="card card-info card-outline">
    <div class="card-body">
        <!-- Filter and Search Row -->
        <div class="row mb-4 align-items-center bg-light p-3 rounded">
            <div class="col-md-3">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Client Name...">
            </div>
            <div class="col-md-3">
                <select id="status-filter" class="form-control" multiple="multiple">
                    <option value="ongoing" selected>Ongoing</option>
                    <option value="completed" selected>Completed</option>
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
                            @foreach($months as $num => $name)
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
            <div class="col-md-2 d-flex justify-content-end align-items-center">
                 <div class="custom-control custom-switch mr-3 pt-1">
                    <input type="checkbox" class="custom-control-input" id="custom-range-switch">
                    <label class="custom-control-label" for="custom-range-switch">Custom</label>
                </div>
                <button class="btn btn-secondary" id="reset-filters">Reset</button>
            </div>
        </div>

        <div id="client-report-table-container">
            @include('Organization.reports._client_report_table', ['groupedTasks' => $groupedTasks])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    $('#status-filter').select2({
        placeholder: 'Filter by Status'
    });

    function fetch_report_data() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $('#client-report-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');

            let data = {
                search: $('#search-input').val(),
                statuses: $('#status-filter').val(),
                use_custom_range: $('#custom-range-switch').is(':checked')
            };

            if (data.use_custom_range) {
                data.start_date = $('#start-date-filter').val();
                data.end_date = $('#end-date-filter').val();
            } else {
                data.year = $('#year-filter').val();
                data.month = $('#month-filter').val();
            }

            $.ajax({
                url: "{{ route('organization.reports.time') }}",
                data: data,
                success: function(response) {
                    $('#client-report-table-container').html(response);
                },
                error: function() {
                    $('#client-report-table-container').html('<p class="text-danger text-center">Failed to load report data.</p>');
                }
            });
        }, 500);
    }

    $('#custom-range-switch').on('change', function() {
        $('#dropdown-filters').toggle(!this.checked);
        $('#custom-range-filters').toggle(this.checked);
        fetch_report_data();
    });
    
    $('#search-input, #status-filter, #year-filter, #month-filter, #start-date-filter, #end-date-filter').on('keyup change', fetch_report_data);
    
    $('#reset-filters').on('click', function() {
        $('#search-input').val('');
        $('#status-filter').val(['ongoing', 'completed']).trigger('change');
        $('#custom-range-switch').prop('checked', false).trigger('change');
        $('#year-filter').val('{{ $currentYear }}');
        $('#month-filter').val('{{ $currentMonth }}');
        fetch_report_data();
    });
});
</script>
@stop