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

{{-- MODIFIED: Modal with the Delete Task button removed --}}
<div id="eventDetailModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h4 style="margin-bottom: 15px;">Task Details</h4>
        <p><strong>Title:</strong> <span id="modalTitle"></span></p>
        <p><strong>Start:</strong> <span id="modalStart"></span></p>
        <p><strong>End:</strong> <span id="modalEnd"></span></p>
        <hr>
        <div class="form-group">
            <label>Event Color</label>
            
            <div id="non-recurring-palette">
                <p class="text-muted small mb-1">For single events</p>
                <div class="d-flex flex-wrap">
                    <span class="color-swatch" data-color="#fd7e14" style="background-color:#fd7e14;"></span>
                    <span class="color-swatch" data-color="#28a745" style="background-color:#28a745;"></span>
                    <span class="color-swatch" data-color="#007bff" style="background-color:#007bff;"></span>
                    <span class="color-swatch" data-color="#6f42c1" style="background-color:#6f42c1;"></span>
                    <span class="color-swatch" data-color="#dc3545" style="background-color:#dc3545;"></span>
                    <span class="color-swatch" data-color="#6c757d" style="background-color:#6c757d;"></span>
                </div>
            </div>

            <div id="recurring-palette" style="display: none;">
                <p class="text-muted small mb-1">For recurring series</p>
                <div class="d-flex flex-wrap">
                    <span class="color-swatch" data-color="#17a2b8" style="background-color:#17a2b8;"></span>
                    <span class="color-swatch" data-color="#0d6efd" style="background-color:#0d6efd;"></span>
                    <span class="color-swatch" data-color="#6610f2" style="background-color:#6610f2;"></span>
                    <span class="color-swatch" data-color="#d63384" style="background-color:#d63384;"></span>
                    <span class="color-swatch" data-color="#ffc107" style="background-color:#ffc107;"></span>
                    <span class="color-swatch" data-color="#343a40" style="background-color:#343a40;"></span>
                </div>
            </div>

            <small id="color-save-status" class="form-text text-success" style="display: none;">Color Saved!</small>
        </div>
        {{-- REMOVED: The <hr> and the delete button are gone --}}
    </div>
</div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        .fc .fc-daygrid-day.fc-day-today { background-color: rgba(255, 229, 100, 0.2); }
        .fc-event { cursor: pointer; border: 1px solid rgba(0,0,0,0.2) !important; }
        .modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: #fff; margin: 15% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 500px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,.5); }
        .close-button { color: #aaa; float: right; font-size: 28px; font-weight: bold; line-height: 1; }
        .close-button:hover, .close-button:focus { color: black; text-decoration: none; cursor: pointer; }
        .color-swatch { width: 30px; height: 30px; border-radius: 50%; margin: 5px; cursor: pointer; border: 3px solid transparent; transition: border-color 0.2s; }
        .color-swatch:hover { border-color: #ccc; }
        .color-swatch.selected { border-color: #000; }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        var calendarEl = document.getElementById('calendar');
        var modal = document.getElementById('eventDetailModal');
        var colorSaveStatus = document.getElementById('color-save-status');
        var modalTitle = document.getElementById('modalTitle');
        var modalStart = document.getElementById('modalStart');
        var modalEnd = document.getElementById('modalEnd');
        // REMOVED: Delete button variable
        var closeButton = document.querySelector('.close-button');
        var nonRecurringPalette = document.getElementById('non-recurring-palette');
        var recurringPalette = document.getElementById('recurring-palette');
        var currentEvent = null;

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            events: '{{ route("organization.calendar.events") }}',
            editable: true,

            eventClick: function(info) {
                currentEvent = info.event;
                modalTitle.textContent = currentEvent.title;
                modalStart.textContent = currentEvent.start.toLocaleString();
                
                const actualEnd = currentEvent.extendedProps.actualEnd;
                modalEnd.textContent = actualEnd ? new Date(actualEnd).toLocaleString() : 'Not set';

                const isRecurring = currentEvent.extendedProps.isRecurring;
                if (isRecurring) {
                    nonRecurringPalette.style.display = 'none';
                    recurringPalette.style.display = 'block';
                } else {
                    nonRecurringPalette.style.display = 'block';
                    recurringPalette.style.display = 'none';
                }
                
                document.querySelectorAll('.color-swatch').forEach(swatch => {
                    swatch.classList.remove('selected');
                    if (swatch.dataset.color === currentEvent.backgroundColor) {
                        swatch.classList.add('selected');
                    }
                });

                modal.style.display = 'block';
            },

            eventDrop: function(info) {
                $.ajax({
                    url: "{{ route('organization.calendar.ajax') }}",
                    type: "POST",
                    data: {
                        type: 'update',
                        id: info.event.id,
                        start: info.event.start.toISOString(),
                        end: info.event.end ? info.event.end.toISOString() : null
                    },
                    error: () => alert('Failed to update event date.')
                });
            }
        });

        calendar.render();
        
        document.querySelectorAll('.color-swatch').forEach(swatch => {
            swatch.addEventListener('click', function() {
                if (!currentEvent) return;
                const newColor = this.dataset.color;
                const eventId = currentEvent.id;

                document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');

                $.ajax({
                    url: "{{ route('organization.calendar.ajax') }}",
                    type: "POST",
                    data: { type: 'update', id: eventId, color: newColor },
                    success: function() {
                        const allEvents = calendar.getEvents();
                        const seriesEvents = allEvents.filter(event => event.id === eventId);
                        seriesEvents.forEach(event => {
                            event.setProp('backgroundColor', newColor);
                            event.setProp('borderColor', newColor);
                        });
                        $(colorSaveStatus).fadeIn().delay(1500).fadeOut();
                    },
                    error: () => alert('Failed to update color.')
                });
            });
        });
        
        // REMOVED: Delete button onclick handler is gone.

        closeButton.onclick = () => { modal.style.display = 'none'; };
        window.onclick = (event) => { if (event.target == modal) { modal.style.display = 'none'; }};
    });
    </script>
@stop