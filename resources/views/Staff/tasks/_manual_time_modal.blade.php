<div class="modal fade" id="manualTimeModal" tabindex="-1" role="dialog" aria-labelledby="manualTimeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form id="manualTimeForm" method="POST" action="">
                @csrf
                @method('POST')

                <div class="modal-header">
                    <h5 class="modal-title" id="manualTimeModalLabel">Add Manual Time</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <p class="mb-3">For task: <strong id="manual-time-task-name"></strong></p>
                    
                    <div class="form-row">
                        <div class="form-group col-6">
                            <label for="manual_hours">Hours</label>
                            <input type="number" id="manual_hours" name="hours" class="form-control" value="0" min="0" required>
                        </div>
                        <div class="form-group col-6">
                            <label for="manual_minutes">Minutes</label>
                            <input type="number" id="manual_minutes" name="minutes" class="form-control" value="0" min="0" max="59" required>
                        </div>
                    </div>

                    <div id="manual-time-feedback" class="text-danger mt-2" style="display: none;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Time</button>
                </div>
            </form>
        </div>
    </div>
</div>