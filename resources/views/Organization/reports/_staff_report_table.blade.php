<div class="accordion" id="staffReportAccordion">
    @forelse($reportData as $staffData)
        <div class="card mb-2">
            <div class="card-header" id="heading-staff-{{ $loop->index }}">
                <h2 class="mb-0 d-flex justify-content-between">
                    <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-staff-{{ $loop->index }}">
                        <strong>{{ $staffData->staff_name }}</strong>
                    </button>
                    <span class="badge badge-info p-2 total-time-badge">
                        Total Time: {{ floor($staffData->total_duration / 3600) }}h {{ floor(($staffData->total_duration % 3600) / 60) }}m
                    </span>
                </h2>
            </div>
            <div id="collapse-staff-{{ $loop->index }}" class="collapse show" data-parent="#staffReportAccordion">
                <div class="card-body">
                    @foreach($staffData->services as $service)
                        <h5>{{ $service['name'] }}</h5>
                        <table class="table table-sm table-bordered mb-4">
                            <thead class="thead-light">
                                <tr>
                                    <th>Job</th>
                                    <th>Task</th>
                                    <th class="text-right">Time Logged</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($service['jobs'] as $job)
                                @foreach($job['tasks'] as $task)
                                <tr>
                                    <td>{{ $job['name'] }}</td>
                                    <td>{{ $task['name'] }}</td>
                                    <td class="text-right">
                                        {{ floor($task['duration'] / 3600) }}h {{ floor(($task['duration'] % 3600) / 60) }}m
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                            </tbody>
                        </table>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <div class="text-center p-4 text-muted">
             <h4>No Data Found</h4>
            <p>No staff members with logged time match the selected criteria.</p>
        </div>
    @endforelse
</div>