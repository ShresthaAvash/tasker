<div class="modal fade" id="taskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskModalLabel">Add/Edit Task</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="task-name">Task Name</label>
                        <input type="text" id="task-name" name="name" class="form-control" required>
                    </div>
                    
                    {{-- RECURRING FIELDS --}}
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_recurring" name="is_recurring" value="1">
                            <label class="custom-control-label" for="is_recurring">Is this a recurring task?</label>
                        </div>
                    </div>

                    <div class="form-group" id="recurring-options" style="display: none;">
                        <label for="recurring_frequency">Recurs every...</label>
                        <select id="recurring_frequency" name="recurring_frequency" class="form-control">
                            <option value="daily">Day</option>
                            <option value="weekly">Week</option>
                            <option value="monthly">Month</option>
                        </select>
                    </div>

                    {{-- CALENDAR FIELDS --}}
                    <hr>
                    <p class="text-muted">Fill out dates to make this task appear on the calendar.</p>
                    <div class="form-group">
                        <label for="task-start">Start Date & Time</label>
                        <input type="datetime-local" id="task-start" name="start" class="form-control">
                        <small class="form-text text-muted">For recurring tasks, this is the first occurrence.</small>
                    </div>

                    <div class="form-group">
                        <label for="task-end">End Date & Time</label>
                        <input type="datetime-local" id="task-end" name="end" class="form-control">
                        <small class="form-text text-muted">Leave blank for a task without a set duration.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>