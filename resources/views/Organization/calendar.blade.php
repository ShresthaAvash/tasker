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
                    <!-- THE CALENDAR -->
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
    {{-- Add the FullCalendar CSS from a CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
@stop

@section('js')
    {{-- Add the FullCalendar JS and its dependencies (Moment.js) from a CDN --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>

    <script>
        $(document).ready(function () {
            // The CSRF token is needed for AJAX POST requests to work with Laravel
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var calendar = $('#calendar').fullCalendar({
                editable: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: '{{ route("organization.calendar") }}',
                selectable: true,
                selectHelper: true,

                // Function to handle creating a new event
                select: function (start, end, allDay) {
                    var title = prompt('Task Name:');
                    if (title) {
                        var start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
                        var end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");
                        $.ajax({
                            url: "{{ route('organization.calendar.ajax') }}",
                            type: "POST",
                            data: {
                                title: title,
                                start: start,
                                end: end,
                                type: 'add'
                            },
                            success: function (data) {
                                // *** THIS IS THE FIX ***
                                // Instead of trying to render the event manually,
                                // we tell the calendar to re-fetch all events from the server.
                                // This is more reliable and guarantees consistency.
                                calendar.fullCalendar('refetchEvents');
                                alert("Event Created Successfully");
                            }
                        });
                    }
                },

                // Function to handle dragging and dropping (updating) an event
                eventDrop: function (event, delta) {
                    var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                    var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");

                    $.ajax({
                        url: "{{ route('organization.calendar.ajax') }}",
                        type: "POST",
                        data: {
                            title: event.title,
                            start: start,
                            end: end,
                            id: event.id,
                            type: 'update'
                        },
                        success: function (response) {
                            alert("Event Updated Successfully");
                        }
                    });
                },

                // Function to handle clicking and deleting an event
                eventClick: function (event) {
                    if (confirm("Are you sure you want to remove it?")) {
                        var id = event.id;
                        $.ajax({
                            url: "{{ route('organization.calendar.ajax') }}",
                            type: "POST",
                            data: {
                                id: id,
                                type: 'delete'
                            },
                            success: function (response) {
                                // Re-fetch events to ensure the view is updated correctly after deletion.
                                calendar.fullCalendar('refetchEvents');
                            }
                        });
                    }
                }
            });
        });
    </script>
@stop