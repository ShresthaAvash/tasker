@if($reportData->isEmpty())
    <div class="text-center text-muted p-5">
        <h4>No Staff Time Logged</h4>
        <p>There is no data to display for the selected criteria.</p>
    </div>
@else
    <div id="accordion-report">
        @foreach($reportData as $staff)
        <div class="card staff-block mb-3">
            <div class="card-header staff-header" id="heading-staff-{{ $loop->index }}">
                <h2 class="mb-0">
                    <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapse-staff-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                        <span><i class="fas fa-user-clock mr-2"></i> Staff: {{ $staff->staff_name }}</span>
                        <span class="time-display">{{ gmdate('H:i:s', $staff->total_duration) }}</span>
                    </button>
                </h2>
            </div>
            <div id="collapse-staff-{{ $loop->index }}" class="collapse {{ $loop->first ? 'show' : '' }}" data-parent="#accordion-report">
                <div class="card-body">
                    @forelse($staff->services as $service)
                    <div class="service-block">
                        {{-- MODIFIED: This is now a clickable link --}}
                        <a href="#collapse-service-{{ Str::slug($staff->staff_name . $service['name']) }}" class="service-header d-flex justify-content-between align-items-center" data-toggle="collapse" aria-expanded="true" style="text-decoration: none;">
                            <span><i class="fas fa-chevron-down collapse-icon mr-2"></i> Service: {{ $service['name'] }}</span>
                            <span class="time-display">{{ gmdate('H:i:s', $service['total_duration']) }}</span>
                        </a>
                        {{-- MODIFIED: This div is now collapsible --}}
                        <div id="collapse-service-{{ Str::slug($staff->staff_name . $service['name']) }}" class="collapse show">
                            <div class="service-body">
                                @foreach($service['jobs'] as $job)
                                <div class="job-block">
                                    <a href="#collapse-job-{{ Str::slug($staff->staff_name . $service['name'] . $job['name']) }}" class="job-header d-flex justify-content-between align-items-center" data-toggle="collapse" aria-expanded="true" style="text-decoration: none;">
                                        <span><i class="fas fa-chevron-down collapse-icon mr-2"></i> Job: {{ $job['name'] }}</span>
                                        <span class="time-display">{{ gmdate('H:i:s', $job['total_duration']) }}</span>
                                    </a>
                                    <div id="collapse-job-{{ Str::slug($staff->staff_name . $service['name'] . $job['name']) }}" class="collapse show">
                                        <div class="list-group list-group-flush">
                                            @foreach($job['tasks'] as $task)
                                            <div class="list-group-item task-list-item">
                                                <span>{{ $task['name'] }}</span>
                                                <span class="time-display">{{ gmdate('H:i:s', $task['duration']) }}</span>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @empty
                        <p class="text-muted p-3">No service data for this staff member in the selected period.</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif