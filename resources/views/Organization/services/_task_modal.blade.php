<div class="modal fade" id="taskModal" tabindex="-1" role="dialog" aria-labelledby="taskModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskModalLabel">Add New Task</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="task-name">Task Name</label>
                        <input type="text" id="task-name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="task-description">Description (optional)</label>
                        <textarea id="task-description" name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Deadline</label>
                        <div class="input-group">
                            <input type="number" id="deadline_offset" name="deadline_offset" class="form-control" value="0" required>
                            <div class="input-group-append">
                                <select id="deadline_unit" name="deadline_unit" class="form-control">
                                    <option value="days">Days</option>
                                    <option value="weeks">Weeks</option>
                                    <option value="months">Months</option>
                                    <option value="years">Years</option>
                                </select>
                            </div>
                        </div>
                        <small class="form-text text-muted">Relative to the job start date.</small>
                    </div>
                    <div class="form-group">
                        <label for="staff_designation_id">Assign to Role (optional)</label>
                        <select id="staff_designation_id" name="staff_designation_id" class="form-control">
                            <option value="">-- Not Assigned --</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- --- NEW CALENDAR FIELDS START HERE --- --}}
                    <hr>
                    <p class="text-muted">Fill out the dates below to make this task appear on the main calendar.</p>
                    <div class="form-group">
                        <label for="task-start">Start Date & Time (for Calendar)</label>
                        <input type="datetime-local" id="task-start" name="start" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="task-end">End Date & Time (for Calendar)</label>
                        <input type="datetime-local" id="task-end" name="end" class="form-control">
                    </div>
                    {{-- --- NEW CALENDAR FIELDS END HERE --- --}}

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </form>
        </div>
    </div>
</div>