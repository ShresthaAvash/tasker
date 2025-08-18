{{-- resources/views/layouts/app.blade.php --}}
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    {{-- This will be correctly overridden by child views like the Tasks page --}}
    <h1>@yield('page_title', 'Dashboard')</h1>
@endsection

@section('content')
    {{-- We remove the old tracker bar from here. The JS will create it. --}}
    @yield('page-content')
@endsection

{{-- We add the new, global JavaScript logic here --}}
@section('js')
<script>
    $(document).ready(function() {
        // ============== GLOBAL TIMER SCRIPT START ==============

        let globalTimerInterval;

        function formatTime(totalSeconds) {
            if (isNaN(totalSeconds) || totalSeconds < 0) totalSeconds = 0;
            const hours = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
            const minutes = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
            const seconds = (totalSeconds % 60).toString().padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        }

        // This function creates and manages the global tracker bar
        function renderGlobalTracker() {
            // Remove any existing tracker to avoid duplicates
            $('#global-live-tracker').remove();

            const timerData = JSON.parse(localStorage.getItem('runningTimer'));

            // If there's no running timer in storage, do nothing.
            if (!timerData) {
                if (globalTimerInterval) clearInterval(globalTimerInterval);
                return;
            }

            // Create the HTML for the tracker bar
            const trackerHtml = `
                <div id="global-live-tracker"
                     class="alert alert-info d-flex justify-content-between align-items-center p-2 mb-4 shadow-sm"
                     style="position: sticky; top: 10px; z-index: 1050; display: none;"
                     role="alert">
                    <div>
                        <i class="fas fa-stopwatch fa-spin mr-2"></i>
                        <span class="font-weight-bold">Tracking:</span>
                        <span class="mx-2">${timerData.taskName}</span>
                        <span id="global-live-tracker-display" class="badge badge-dark" style="font-size: 1.1em; min-width: 80px;">00:00:00</span>
                    </div>
                    <button id="global-live-tracker-stop-btn" data-task-id="${timerData.taskId}" class="btn btn-danger btn-sm">
                        <i class="fas fa-stop"></i> Stop Timer
                    </button>
                </div>
            `;

            // Add the tracker to the top of the main content area and fade it in
            $('.content-wrapper .content').prepend(trackerHtml);
            $('#global-live-tracker').fadeIn();

            // Start the live timer interval
            const duration = parseInt(timerData.duration, 10) || 0;
            const startTime = new Date(timerData.startedAt).getTime();
            const display = $('#global-live-tracker-display');

            if (globalTimerInterval) clearInterval(globalTimerInterval);

            const updateDisplay = () => {
                const now = new Date().getTime();
                const elapsed = Math.floor((now - startTime) / 1000);
                display.text(formatTime(duration + elapsed));
            };

            updateDisplay();
            globalTimerInterval = setInterval(updateDisplay, 1000);
        }

        // Event handler for the GLOBAL stop button
        $(document).on('click', '#global-live-tracker-stop-btn', function() {
            const button = $(this);
            const taskId = button.data('task-id');
            
            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Stopping...');

            $.ajax({
                type: 'POST',
                url: `/staff/tasks/${taskId}/stop-timer`,
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    // On success, clear the storage and remove the bar
                    localStorage.removeItem('runningTimer');
                    if (globalTimerInterval) clearInterval(globalTimerInterval);
                    $('#global-live-tracker').fadeOut(400, () => $(this).remove());
                    
                    // If we are on the tasks page, reload it to update the main list
                    if (window.location.pathname.includes('/staff/tasks')) {
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    alert('Error: Could not stop the timer. Please refresh the page.');
                    button.prop('disabled', false).html('<i class="fas fa-stop"></i> Stop Timer');
                }
            });
        });

        // Run the function on every page load to check if a tracker should be displayed
        renderGlobalTracker();
        
        // Make the render function globally accessible so the tasks page can call it
        window.renderGlobalTracker = renderGlobalTracker;

        // ============== GLOBAL TIMER SCRIPT END ==============
    });
</script>
{{-- This allows the tasks page to add its own specific JS --}}
@yield('page_content_js')
@endsection