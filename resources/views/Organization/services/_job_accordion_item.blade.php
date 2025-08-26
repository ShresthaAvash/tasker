<div class="card mb-2" id="job-card-{{ $job->id }}">
    <div class="card-header bg-light p-2" id="heading-{{ $job->id }}">
        <div class="d-flex w-100 align-items-center">
            <a href="#collapse-{{ $job->id }}" data-toggle="collapse" aria-expanded="true" aria-controls="collapse-{{ $job->id }}" class="d-block text-dark flex-grow-1 text-decoration-none p-1">
                <h5 class="mb-0 font-weight-bold">
                    <i class="fas fa-chevron-up collapse-icon mr-2"></i>
                    <span id="job-title-{{ $job->id }}">{{ $job->name }}</span>
                </h5>
            </a>
            <div class="job-actions ml-auto">
                <button class="btn btn-sm btn-light border" data-toggle="modal" data-target="#jobModal" data-action="edit" data-job='@json($job)'>
                    <i class="fas fa-pencil-alt text-warning"></i> Edit Job
                </button>
                <button class="btn btn-sm btn-light border delete-job-btn ml-1" data-job-id="{{ $job->id }}">
                    <i class="fas fa-trash text-danger"></i> Delete Job
                </button>
            </div>
        </div>
    </div>

    <div id="collapse-{{ $job->id }}" class="collapse show" aria-labelledby="heading-{{ $job->id }}">
        <div class="card-body pt-3 pb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-uppercase text-muted small font-weight-bold">TASKS</h6>
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#taskModal" data-action="create" data-jobid="{{ $job->id }}">
                    <i class="fas fa-plus"></i> Add Task
                </button>
            </div>
            <table class="table table-hover task-list-table">
                <tbody id="tasks-in-job-{{ $job->id }}">
                    @forelse($job->tasks as $task)
                        <tr class="task-row" id="task-row-{{ $task->id }}">
                            <td class="task-name-cell pl-0">{{ $task->name }}</td>
                            <td class="text-right">
                                <button class="btn btn-sm btn-light border" data-toggle="modal" data-target="#taskModal" data-action="edit" data-task='@json($task)'>
                                    <i class="fas fa-edit text-warning"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-light border delete-task-btn ml-2" data-task-id="{{ $task->id }}">
                                    <i class="fas fa-trash text-danger"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">No tasks have been added to this job yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>