@extends('adminlte::page')

@section('title', 'Task Calendar')

@section('content_header')
    <h1>Task Calendar</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-body p-0">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>

    <script>
        $(document).ready(function () {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            console.log('Calendar script initializing...'); // DEBUG

            $('#calendar').fullCalendar({
                editable: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                
                // --- THIS IS THE NEW DEBUGGING-ENABLED EVENT LOADER ---
                events: function(start, end, timezone, callback) {
                    console.log('Calendar is requesting events...'); // DEBUG
                    $.ajax({
                        url: '{{ route("organization.calendar") }}',
                        type: 'GET',
                        data: {
                            // FullCalendar sends start and end dates automatically.
                            start: start.format('YYYY-MM-DD'),
                            end: end.format('YYYY-MM-DD')
                        },
                        success: function(data) {
                            console.log('Successfully fetched calendar events from server.'); // DEBUG
                            console.log(data); // DEBUG: This shows the raw data in the console.
                            callback(data); // This passes the events to the calendar to be drawn.
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("--- ERROR FETCHING CALENDAR EVENTS ---"); // DEBUG
                            console.error("Status: " + textStatus); // DEBUG
                            console.error("Error: " + errorThrown); // DEBUG
                            console.error("Response Text: " + jqXHR.responseText); // DEBUG
                            alert('Failed to load calendar events. Check the browser console (F12) for details.');
                        }
                    });
                },

                // The rest of the functions for adding, dragging, deleting...
                selectable: true,
                selectHelper: true,
                select: function (start, end, allDay) {
                    var title = prompt('Task Name:');
                    if (title) {
                        var start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
                        var end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");
                        $.ajax({
                            url: "{{ route('organization.calendar.ajax') }}",
                            type: "POST",
                            data: { title: title, start: start, end: end, type: 'add' },
                            success: function (data) {
                                $('#calendar').fullCalendar('refetchEvents');
                                alert("Event Created Successfully");
                            }
                        });
                    }
                },
                eventDrop: function (event, delta) {
                    var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                    var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                    $.ajax({
                        url: "{{ route('organization.calendar.ajax') }}",
                        type: "POST",
                        data: { title: event.title, start: start, end: end, id: event.id, type: 'update' },
                        success: function (response) { alert("Event Updated Successfully"); }
                    });
                },
                eventClick: function (event) {
                    if (confirm("Are you sure you want to remove it?")) {
                        $.ajax({
                            url: "{{ route('organization.calendar.ajax') }}",
                            type: "POST",
                            data: { id: event.id, type: 'delete' },
                            success: function (response) { $('#calendar').fullCalendar('refetchEvents'); }
                        });
                    }
                }
            });
        });
    </script>
@stop