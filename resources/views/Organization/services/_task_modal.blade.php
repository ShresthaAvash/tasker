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
                    {{-- Basic Task Info --}}
                    <div class="form-group">
                        <label for="task-name">Task Name</label>
                        <input type="text" id="task-name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="task-description">Description</label>
                        <textarea id="task-description" name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="staff_designation_id">Default Designation</label>
                        <select id="staff_designation_id" name="staff_designation_id" class="form-control">
                            <option value="">-- No Default Designation --</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Recurring Fields --}}
                    <hr>
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
                            <option value="yearly">Year</option>
                        </select>
                    </div>

                    {{-- Calendar & Date Fields --}}
                    <hr>
                    <p class="text-muted">Set dates for scheduling and calendar visibility.</p>
                    <div class="form-group">
                        <label for="task-start">Start Date & Time</label>
                        <input type="datetime-local" id="task-start" name="start" class="form-control" required>
                        <small class="form-text text-muted">For recurring tasks, this is the first occurrence. Required for all tasks.</small>
                    </div>
                    <div class="form-group">
                        <label for="task-end">End Date & Time</label>
                        <input type="datetime-local" id="task-end" name="end" class="form-control">
                        <small class="form-text text-muted" id="end-date-help">For recurring tasks, this sets the recurrence end date. For non-recurring, it's the task's duration (optional).</small>
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