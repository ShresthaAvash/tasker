<div class="card card-outline card-secondary mb-3" id="job-card-{{ $job->id }}" data-job='@json($job)'>
    <div class="card-header" id="heading-{{ $job->id }}">
        <a href="#collapse-{{ $job->id }}" data-toggle="collapse" aria-expanded="true" aria-controls="collapse-{{ $job->id }}" class="d-block text-dark">
            <div class="d-flex w-100 align-items-center">
                <h5 class="mb-0 font-weight-bold flex-grow-1">
                    <i class="fas fa-chevron-down collapse-icon mr-2"></i>
                    <span id="job-title-{{ $job->id }}">{{ $job->name }}</span>
                </h5>
                <div class="card-tools ml-auto">
                    {{-- --- THIS IS THE FIX: The inline onclick="..." attributes have been removed --- --}}
                    <button class="btn btn-sm btn-light border" data-toggle="modal" data-target="#jobModal" data-action="edit" data-job='@json($job)'>
                        <i class="fas fa-pencil-alt text-warning"></i> Edit Job
                    </button>
                    <button class="btn btn-sm btn-light border text-danger delete-job-btn ml-1" data-job-id="{{ $job->id }}">
                        <i class="fas fa-trash"></i> Delete Job
                    </button>
                </div>
            </div>
        </a>
    </div>

    <div id="collapse-{{ $job->id }}" class="collapse show" aria-labelledby="heading-{{ $job->id }}">
        <div class="card-body pt-3 pb-2">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="text-muted font-weight-bold">TASKS</h6>
                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#taskModal" data-action="create" data-jobid="{{ $job->id }}">
                    <i class="fas fa-plus"></i> Add Task
                </button>
            </div>
            <table class="table table-hover task-list-table">
                <tbody id="tasks-in-job-{{ $job->id }}">
                    @forelse($job->tasks as $task)
                        <tr class="task-row" id="task-row-{{ $task->id }}">
                            <td class="task-name-cell pl-0">{{ $task->name }}</td>
                            <td class="text-right">
                                <button class="btn btn-sm btn-light border text-warning" data-toggle="modal" data-target="#taskModal" data-action="edit" data-task='@json($task)'>
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-light border text-danger delete-task-btn ml-2" data-task-id="{{ $task->id }}">
                                    <i class="fas fa-trash"></i> Delete
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