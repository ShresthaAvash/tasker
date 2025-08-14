@extends('adminlte::page')

@section('title', 'Calendar')

@section('content_header')
    <h1>Calendar</h1>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-body p-3">
        <div id="calendar"></div>
    </div>
</div>

{{-- Modal for showing event details, color picker, and delete button --}}
<div id="eventDetailModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h4 style="margin-bottom: 15px;">Task Details</h4>
        <p><strong>Title:</strong> <span id="modalTitle"></span></p>
        <p><strong>Start:</strong> <span id="modalStart"></span></p>
        <p><strong>End:</strong> <span id="modalEnd"></span></p>
        <hr>
        <div class="form-group">
            <label for="eventColor">Title Color</label>
            <div class="input-group">
                <input type="color" id="eventColor" class="form-control form-control-color">
                <div class="input-group-append">
                    <button id="saveColorButton" class="btn btn-outline-secondary">Save Color</button>
                </div>
            </div>
            <small id="color-save-status" class="form-text text-success" style="display: none;">Saved!</small>
        </div>
        <hr>
        <button id="deleteEventButton" class="delete-btn"><i class="fa fa-trash"></i> Delete Task</button>
    </div>
</div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        .fc .fc-daygrid-day.fc-day-today { background-color: rgba(255, 229, 100, 0.2); }
        .fc-event { cursor: pointer; }
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fff; margin: 15% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 500px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,.5); }
        .close-button { color: #aaa; float: right; font-size: 28px; font-weight: bold; line-height: 1; }
        .close-button:hover, .close-button:focus { color: black; text-decoration: none; cursor: pointer; }
        .delete-btn { background-color: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 15px; width: 100%; }
        .form-control-color { height: calc(2.25rem + 2px); }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        var calendarEl = document.getElementById('calendar');
        var modal = document.getElementById('eventDetailModal');
        var colorPicker = document.getElementById('eventColor');
        var saveColorButton = document.getElementById('saveColorButton');
        var colorSaveStatus = document.getElementById('color-save-status');
        var modalTitle = document.getElementById('modalTitle');
        var modalStart = document.getElementById('modalStart');
        var modalEnd = document.getElementById('modalEnd');
        var deleteButton = document.getElementById('deleteEventButton');
        var closeButton = document.querySelector('.close-button');
        var currentEvent = null;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            events: '{{ route("organization.calendar.events") }}',
            eventTimeFormat: { hour: 'numeric', minute: '2-digit', meridiem: 'short' },

            eventClick: function(info) {
                currentEvent = info.event;
                modalTitle.textContent = currentEvent.title;
                modalStart.textContent = currentEvent.start.toLocaleString();
                
                // --- DEFINITIVE FIX ---
                // Use the 'actualEnd' property from the backend for accurate display
                const actualEnd = currentEvent.extendedProps.actualEnd;
                modalEnd.textContent = actualEnd ? new Date(actualEnd).toLocaleString() : 'Not set';

                colorPicker.value = currentEvent.textColor || '#FFFFFF';
                modal.style.display = 'block';
            }
        });

        calendar.render();

        saveColorButton.onclick = function() {
            if (!currentEvent) return;
            const newColor = colorPicker.value;
            $.ajax({
                url: "{{ route('organization.calendar.ajax') }}",
                type: "POST",
                data: { id: currentEvent.id, type: 'updateColor', color: newColor },
                success: function() {
                    currentEvent.setProp('textColor', newColor);
                    $(colorSaveStatus).fadeIn().delay(1500).fadeOut();
                },
                error: () => alert('Failed to update color.')
            });
        };
        
        deleteButton.onclick = function() {
            if (currentEvent && confirm('Are you sure you want to delete this task?')) {
                $.ajax({
                    url: "{{ route('organization.calendar.ajax') }}",
                    type: "POST",
                    data: { id: currentEvent.id, type: 'delete' },
                    success: function() {
                        currentEvent.remove();
                        modal.style.display = 'none';
                    },
                    error: () => alert("Error: Could not delete the task.")
                });
            }
        };

        closeButton.onclick = () => { modal.style.display = 'none'; };
        window.onclick = (event) => { if (event.target == modal) { modal.style.display = 'none'; }};
    });
    </script>
@stop