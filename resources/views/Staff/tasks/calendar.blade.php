@extends('layouts.app')

@section('title', 'My Calendar')

@section('page_title', 'My Calendar')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>My Calendar</h1>
        <div class="d-flex align-items-center">
            <div class="form-inline mr-3">
                <label for="month-select" class="mr-2 font-weight-normal">Go to:</label>
                <select id="month-select" class="form-control form-control-sm"></select>
                <select id="year-select" class="form-control form-control-sm ml-2"></select>
            </div>
            <a href="{{ route('staff.tasks.index') }}" class="btn btn-primary">
                <i class="fas fa-tasks mr-1"></i> View Task List
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="card card-primary">
    <div class="card-body p-3">
        <div id="calendar"></div>
    </div>
</div>

{{-- Modal for event details --}}
<div id="eventDetailModal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h4 style="margin-bottom: 15px;">Task Details</h4>
        <p><strong>Title:</strong> <span id="modalTitle"></span></p>
        <p><strong>Service:</strong> <span id="modalService"></span></p>
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
        <hr>
        <a href="#" id="viewInListBtn" class="btn btn-primary btn-block"><i class="fas fa-list-alt mr-2"></i>View in Task List</a>
    </div>
</div>
@endsection

@section('css')
@parent
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
        #calendar {
            height: 85vh !important;
        }

        .highlight-event {
            border: 3px solid black !important;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.7);
            z-index: 9999;
        }
    </style>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var modal = document.getElementById('eventDetailModal');
        var colorSaveStatus = document.getElementById('color-save-status');
        var modalTitle = document.getElementById('modalTitle');
        var modalService = document.getElementById('modalService');
        var modalStart = document.getElementById('modalStart');
        var modalEnd = document.getElementById('modalEnd');
        var viewInListBtn = document.getElementById('viewInListBtn');
        var closeButton = document.querySelector('.close-button');
        var nonRecurringPalette = document.getElementById('non-recurring-palette');
        var recurringPalette = document.getElementById('recurring-palette');
        var currentEvent = null;

        const urlParams = new URLSearchParams(window.location.search);
        const eventIdToHighlight = urlParams.get('event_id');
        const dateToGoTo = urlParams.get('date');
        let highlighted = false;

        const monthSelect = document.getElementById('month-select');
        const yearSelect = document.getElementById('year-select');

        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        months.forEach((month, index) => {
            const option = document.createElement('option');
            option.value = index;
            option.textContent = month;
            monthSelect.appendChild(option);
        });

        const currentYear = new Date().getFullYear();
        for (let i = currentYear - 5; i <= currentYear + 5; i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = i;
            yearSelect.appendChild(option);
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            initialDate: dateToGoTo || new Date(),
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: '{{ route("staff.calendar.events") }}',
            editable: true,
            height: '100%',
            
            datesSet: function(info) {
                const currentDate = info.view.currentStart;
                monthSelect.value = currentDate.getMonth();
                yearSelect.value = currentDate.getFullYear();
            },

            eventDidMount: function(info) {
                if (!highlighted && info.event.id === eventIdToHighlight) {
                    const eventElement = info.el;
                    eventElement.classList.add('highlight-event');
                    eventElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    const removeHighlight = () => {
                        if (eventElement) {
                            eventElement.classList.remove('highlight-event');
                        }
                        document.body.removeEventListener('click', removeHighlight, { capture: true });
                    };
                    
                    document.body.addEventListener('click', removeHighlight, { once: true, capture: true });
                    
                    if (history.replaceState) {
                        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
                    }
                    highlighted = true;
                }
            },

            eventClick: function(info) {
                currentEvent = info.event;
                modalTitle.textContent = currentEvent.title;
                modalService.textContent = currentEvent.extendedProps.serviceName || 'N/A';
                modalStart.textContent = currentEvent.start.toLocaleString();
                
                const actualEnd = currentEvent.extendedProps.actualEnd;
                modalEnd.textContent = actualEnd ? new Date(actualEnd).toLocaleString() : 'Not set';
                
                const taskListPageUrl = "{{ route('staff.tasks.index') }}";
                const eventId = currentEvent.id;
                const eventDate = currentEvent.start; 
                const eventYear = eventDate.getFullYear();
                const eventMonth = eventDate.getMonth() + 1;

                const viewUrl = new URL(taskListPageUrl);
                viewUrl.searchParams.append('task_id', eventId);
                viewUrl.searchParams.append('year', eventYear);
                viewUrl.searchParams.append('month', eventMonth);
                viewInListBtn.href = viewUrl.toString();

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
                    url: "{{ route('staff.calendar.ajax') }}",
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
        
        function navigateCalendar() {
            const year = parseInt(yearSelect.value, 10);
            const month = parseInt(monthSelect.value, 10);
            const newDate = new Date(year, month, 1);
            calendar.gotoDate(newDate);
        }
        monthSelect.addEventListener('change', navigateCalendar);
        yearSelect.addEventListener('change', navigateCalendar);

        document.querySelectorAll('.color-swatch').forEach(swatch => {
            swatch.addEventListener('click', function() {
                if (!currentEvent) return;
                const newColor = this.dataset.color;
                const eventId = currentEvent.id;

                document.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');

                $.ajax({
                    url: "{{ route('staff.calendar.ajax') }}",
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
        
        closeButton.onclick = () => { modal.style.display = 'none'; };
        window.onclick = (event) => { if (event.target == modal) { modal.style.display = 'none'; }};
    });
    </script>
@endpush