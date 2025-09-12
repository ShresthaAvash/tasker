@if($client->assignedServices->isNotEmpty())
<script>
    $(function() {
        $('#service-selection-step').hide();
        $('#task-config-step').show();
        $('#next-to-tasks-btn').trigger('click');
    });
</script>
@endif
<form id="service-assignment-form" action="{{ route('clients.services.assign', $client) }}" method="POST">
    @csrf
    <div id="service-selection-step">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4>1. Select Services</h4>
                <p>Choose the services you want to activate for this client.</p>
            </div>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#createServiceModal">
                <i class="fas fa-plus"></i> Create New Service
            </button>
        </div>
        <hr>
        {{-- NEW: "Select All" checkbox --}}
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="select-all-services">
                <label for="select-all-services" class="custom-control-label font-weight-bold">Select All Services</label>
            </div>
        </div>
        <div class="form-group" id="service-checkbox-list">
            @forelse($allServices as $service)
                <div class="custom-control custom-checkbox">
                    {{-- MODIFIED: Removed the conditional checked attribute. JS will handle it. --}}
                    <input class="custom-control-input service-checkbox" type="checkbox" id="service_{{ $service->id }}" name="services[]" value="{{ $service->id }}">
                    <label for="service_{{ $service->id }}" class="custom-control-label">{{ $service->name }}</label>
                </div>
            @empty
                <p class="text-muted">No services have been created yet. <a href="{{ route('services.create') }}">Create one now</a>.</p>
            @endforelse
        </div>
        <button type="button" id="next-to-tasks-btn" class="btn btn-info">Next <i class="fas fa-arrow-right"></i></button>
    </div>

    <div id="task-config-step" style="display: none;">
        <h4>2. Configure Tasks</h4>
        <p>Uncheck any tasks to exclude them. Assign staff members and dates to each task.</p>
        <div id="tasks-accordion-container" class="accordion"></div>
        <hr>
        <button type="button" id="back-to-services-btn" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Save Assignments</button>
    </div>
</form>