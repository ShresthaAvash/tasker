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

<!-- NEW: Custom Modal for displaying event details and deletion -->
<div id="eventDetailModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2 style="margin-bottom: 15px;">Task Details</h2>
        <p><strong>Title:</strong> <span id="modalTitle"></span></p>
        <p><strong>Start:</strong> <span id="modalStart"></span></p>
        <p><strong>End:</strong> <span id="modalEnd"></span></p>
        <button id="deleteEventButton" class="delete-btn">
            <i class="fa fa-trash"></i> Delete Task
        </button>
    </div>
</div>
@stop

@section('css')
    {{-- FullCalendar Core CSS --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.css" />
    
    {{-- NEW: CSS for the custom modal --}}
    <style>
        /* Modal background overlay */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1050; /* Sit on top of everything */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.6); /* Black w/ opacity */
        }

        /* Modal content box */
        .modal-content {
            background-color: #fff;
            color: #333;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 25px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px; /* Responsive width */
            border-radius: 8px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,.5);
        }

        /* The Close Button in the top right */
        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            line-height: 1;
        }

        .close-button:hover,
        .close-button:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Red Delete Button */
        .delete-btn {
            background-color: #dc3545; /* Bootstrap's danger red */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 15px;
            transition: background-color 0.2s;
        }

        .delete-btn:hover {
            background-color: #c82333; /* A darker red on hover */
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>

    <script>
        $(document).ready(function () {
            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            
            // Get modal elements once
            var modal = $('#eventDetailModal');
            var modalTitle = $('#modalTitle');
            var modalStart = $('#modalStart');
            var modalEnd = $('#modalEnd');
            var deleteButton = $('#deleteEventButton');
            var closeButton = $('.close-button');

            // --- Calendar Initialization ---
            $('#calendar').fullCalendar({
                editable: true,
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: '{{ route("organization.calendar") }}', // Simplified event loading

                // REMOVED: The `select` callback is gone, so clicking empty days does nothing.
                // selectable: false, // This is the default, so no need to declare it.

                // --- Event Drag-and-Drop ---
                eventDrop: function (event, delta) {
                    var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
                    var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
                    $.ajax({
                        url: "{{ route('organization.calendar.ajax') }}",
                        type: "POST",
                        data: { title: event.title, start: start, end: end, id: event.id, type: 'update' },
                        success: function (response) { 
                            // Using a more modern notification can be a future improvement.
                            // For now, an alert provides simple feedback.
                            alert("Event Updated Successfully"); 
                        }
                    });
                },

                // --- NEW: Event Click opens the custom modal ---
                eventClick: function (event) {
                    // Populate the modal with the event's data
                    modalTitle.text(event.title);
                    modalStart.text(moment(event.start).format('MMM D, YYYY h:mm A'));
                    modalEnd.text(event.end ? moment(event.end).format('MMM D, YYYY h:mm A') : 'N/A');

                    // Store the event ID on the delete button's data attribute
                    deleteButton.data('eventId', event.id);

                    // Show the modal
                    modal.show();
                }
            });

            // --- Modal Control Logic ---

            // When the user clicks the red delete button
            deleteButton.on('click', function() {
                var eventId = $(this).data('eventId');
                if (eventId) {
                    $.ajax({
                        url: "{{ route('organization.calendar.ajax') }}",
                        type: "POST",
                        data: { id: eventId, type: 'delete' },
                        success: function (response) { 
                            modal.hide(); // Hide the modal on success
                            $('#calendar').fullCalendar('removeEvents', eventId); // Remove event from view
                            alert("Event Deleted Successfully");
                        },
                        error: function() {
                            alert("Error: Could not delete the event.");
                        }
                    });
                }
            });

            // When the user clicks on <span> (x), close the modal
            closeButton.on('click', function() {
                modal.hide();
            });

            // When the user clicks anywhere outside of the modal, close it
            $(window).on('click', function(event) {
                if ($(event.target).is(modal)) {
                    modal.hide();
                }
            });
        });
    </script>
@stop