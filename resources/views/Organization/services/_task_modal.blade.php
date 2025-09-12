<div class="modal fade" id="taskModal" tabindex="-1" role="dialog">
<div class="modal-dialog modal-lg" role="document">
<div class="modal-content">
<form method="POST" action="">
@csrf
<input type="hidden" name="_method" value="POST">
{{-- MODIFIED: Changed bg-info to bg-primary for the blue theme --}}
<div class="modal-header bg-primary text-white">
<h5 class="modal-title" id="taskModalLabel">Add/Edit Task</h5>
<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
</div>
<div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
<!-- Basic Info Section -->
<h6 class="font-weight-bold">Basic Information</h6>
<hr class="mt-1 mb-3">
<div class="form-group mb-4">
<label for="task-name" class="font-weight-normal">Task Name</label>
<input type="text" id="task-name" name="name" class="form-control" placeholder="e.g., Collect Bank Statements" required>
</div>
<div class="form-group mb-4">
<label for="task-description" class="font-weight-normal">Description <small class="text-muted">(Optional)</small></label>
<textarea id="task-description" name="description" class="form-control" rows="3" placeholder="Add any details or instructions about the task..."></textarea>
</div>
<!-- Scheduling Section -->
                <h6 class="font-weight-bold mt-4">Scheduling</h6>
                <hr class="mt-1 mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="task-start" class="font-weight-normal">Default Start Date & Time</label>
                            <input type="datetime-local" id="task-start" name="start" class="form-control">
                            <small class="form-text text-muted" id="start-date-help">Optional. When the task should begin.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                         <div class="form-group">
                            <label for="task-end" class="font-weight-normal">Default End Date & Time</label>
                            <input type="datetime-local" id="task-end" name="end" class="form-control">
                            <small class="form-text text-muted" id="end-date-help">Optional. When the task is due.</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                {{-- MODIFIED: Changed btn-info to btn-primary for the blue theme --}}
                <button type="submit" class="btn btn-primary px-4">Save Task</button>
            </div>
        </form>
    </div>
</div>
</div>