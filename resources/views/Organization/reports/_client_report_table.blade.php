<div class="accordion" id="clientReportAccordion">
    @php $clientIndex = 0; @endphp
    @forelse($groupedTasks as $clientName => $services)
        @php
            $clientTotalDuration = 0;
            foreach ($services as $jobs) {
                foreach ($jobs as $tasks) {
                    $clientTotalDuration += $tasks->sum('duration_in_seconds');
                }
            }
        @endphp
        <div class="card mb-2">
            <div class="card-header" id="heading-client-{{ $clientIndex }}">
                <h2 class="mb-0 d-flex justify-content-between">
                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-client-{{ $clientIndex }}">
                        <strong>{{ $clientName }}</strong>
                    </button>
                    <span class="badge badge-info p-2 total-time-badge">
                        Total Time: {{ floor($clientTotalDuration / 3600) }}h {{ floor(($clientTotalDuration % 3600) / 60) }}m
                    </span>
                </h2>
            </div>
            <div id="collapse-client-{{ $clientIndex }}" class="collapse show" data-parent="#clientReportAccordion">
                <div class="card-body">
                    @foreach($services as $serviceName => $jobs)
                        <h5>{{ $serviceName }}</h5>
                        <table class="table table-sm table-bordered mb-4">
                            <thead class="thead-light">
                                <tr>
                                    <th>Job</th>
                                    <th>Task</th>
                                    <th>Assigned Staff</th>
                                    <th>Status</th>
                                    <th class="text-right">Time Logged</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobs as $jobName => $tasks)
                                    @foreach($tasks as $task)
                                    <tr>
                                        <td>{{ $jobName }}</td>
                                        <td>{{ $task->name }}</td>
                                        <td>{{ $task->staff->pluck('name')->implode(', ') }}</td>
                                        <td><span class="badge badge-{{ $task->status == 'completed' ? 'success' : 'primary' }}">{{ ucfirst($task->status) }}</span></td>
                                        <td class="text-right">{{ floor($task->duration_in_seconds / 3600) }}h {{ floor(($task->duration_in_seconds % 3600) / 60) }}m</td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                </div>
            </div>
        </div>
        @php $clientIndex++; @endphp
    @empty
        <div class="text-center p-4 text-muted">
            <h4>No Tasks Found</h4>
            <p>There are no ongoing or completed tasks that match the selected criteria.</p>
        </div>
    @endforelse
</div>