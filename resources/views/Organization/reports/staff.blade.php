@extends('layouts.app')

@section('title', 'Staff Report')

@section('css')
    @parent {{-- This inherits the base styles from the layout --}}
    <style>
        .period-buttons .btn.active {
            background-color: #17a2b8 !important;
            border-color: #17a2b8 !important;
            color: white;
        }
        .total-time-badge {
            font-size: 1em;
        }
    </style>
@stop

@section('content_header')
     <div class="d-flex justify-content-between align-items-center">
        <h1>Staff Report</h1>
        <button class="btn btn-primary" onclick="window.print();"><i class="fas fa-print"></i> Print Report</button>
    </div>
@stop

@section('content')
<div class="card card-info card-outline">
    <div class="card-body">
        <div class="row mb-4 align-items-center bg-light p-3 rounded">
            <div class="col-md-4">
                <input type="text" id="search-input" class="form-control" placeholder="Search by Staff Name...">
            </div>
            <div class="col-md-8 d-flex justify-content-end align-items-center">
                <div class="btn-group btn-group-toggle period-buttons mr-3" data-toggle="buttons">
                    <label class="btn btn-outline-secondary" data-period="day"><input type="radio" name="period" value="day"> Day</label>
                    <label class="btn btn-outline-secondary" data-period="week"><input type="radio" name="period" value="week"> Week</label>
                    <label class="btn btn-outline-secondary active" data-period="month"><input type="radio" name="period" value="month" checked> Month</label>
                    <label class="btn btn-outline-secondary" data-period="year"><input type="radio" name="period" value="year"> Year</label>
                    <label class="btn btn-outline-secondary" data-period="all"><input type="radio" name="period" value="all"> All Time</label>
                </div>
                <div id="custom-range-picker" style="display: none;">
                     <input type="date" id="start-date" class="form-control d-inline-block" style="width: auto;">
                     <span class="mx-1">to</span>
                     <input type="date" id="end-date" class="form-control d-inline-block" style="width: auto;">
                </div>
                 <button class="btn btn-secondary ml-2" id="custom-period-btn">Custom</button>
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

    function formatTime(totalSeconds) {
        if (isNaN(totalSeconds) || totalSeconds < 0) totalSeconds = 0;
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        return `${hours}h ${minutes}m`;
    }
    window.formatTime = formatTime;

    function fetch_report_data() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $('#staff-report-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
            
            let data = {
                search: $('#search-input').val(),
                period: $('.period-buttons .active').data('period') || 'month'
            };

            if (data.period === 'custom') {
                data.start_date = $('#start-date').val();
                data.end_date = $('#end-date').val();
            }

            $.ajax({
                url: "{{ route('organization.reports.staff') }}",
                data: data,
                success: function(response) {
                    $('#staff-report-table-container').html(response);
                },
                error: function() {
                    $('#staff-report-table-container').html('<p class="text-danger text-center">Failed to load report data.</p>');
                }
            });
        }, 500);
    }
    
    $('.period-buttons label').on('click', function() {
        $('#custom-range-picker').hide();
        setTimeout(fetch_report_data, 50);
    });
    
    $('#custom-period-btn').on('click', function() {
        $('.period-buttons label').removeClass('active');
        $(this).addClass('active');
        $('#custom-range-picker').show();
    });

    $('#start-date, #end-date').on('change', function() {
        const start = $('#start-date').val();
        const end = $('#end-date').val();
        if(start && end) {
            $('.period-buttons .btn').removeClass('active');
            $('#custom-period-btn').addClass('active');
            $('.period-buttons label').removeClass('active');
            
            let periodData = $('.period-buttons .active');
            periodData.data('period', 'custom');

            fetch_report_data();
        }
    });
    
    $('#search-input').on('keyup', fetch_report_data);

});
</script>
@stop