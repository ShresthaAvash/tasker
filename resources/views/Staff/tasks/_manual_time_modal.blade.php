<div class="modal fade" id="manualTimeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="manualTimeForm" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Manual Time</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>For task: <strong id="manual-time-task-name"></strong></p>
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
                    <div id="manual-time-errors" class="text-danger"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Time</button>
                </div>
            </form>
        </div>
    </div>
</div>