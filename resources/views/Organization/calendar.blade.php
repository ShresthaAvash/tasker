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
            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

            $('#calendar').fullCalendar({
                editable: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                
                // --- THIS IS THE FIX ---
                // This correctly points to the route that loads events for ANY authorized user.
                events: '{{ route("organization.calendar") }}',

                selectable: true,
                selectHelper: true,
                
                // This function runs when you click on an empty day/time slot
                select: function (start, end, allDay) {
                    var title = prompt('New Task Name:');
                    if (title) {
                        var start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
                        var end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");
                        $.ajax({
                            url: "{{ route('organization.calendar.ajax') }}",
                            type: "POST",
                            data: { title: title, start: start, end: end, type: 'add' },
                            success: function (data) {
                                $('#calendar').fullCalendar('refetchEvents');
                                alert("Task Created Successfully");
                            }
                        });
                    }
                },

                // This function runs when you drag and drop an event
                eventDrop: function (event, delta) {
                    var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                    var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                    $.ajax({
                        url: "{{ route('organization.calendar.ajax') }}",
                        type: "POST",
                        data: { title: event.title, start: start, end: end, id: event.id, type: 'update' },
                        success: function (response) { 
                            alert("Task Updated Successfully"); 
                        }
                    });
                },

                // This function runs when you click on an existing event
                eventClick: function (event) {
                    if (confirm("Are you sure you want to delete this task?")) {
                        $.ajax({
                            url: "{{ route('organization.calendar.ajax') }}",
                            type: "POST",
                            data: { id: event.id, type: 'delete' },
                            success: function (response) { 
                                $('#calendar').fullCalendar('refetchEvents'); 
                            }
                        });
                    }
                }
            });
        });
    </script>
@stop