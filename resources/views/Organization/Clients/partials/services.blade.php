{{-- tasker/resources/views/Organization/Clients/partials/services.blade.php --}}
@if($client->assignedServices->isNotEmpty())
<script>
    $(function() {
        $('#service-selection-step').hide();
        $('#job-config-step').show();
        $('#next-to-jobs-btn').trigger('click');
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
        <div class="form-group" id="service-checkbox-list">
            @forelse($allServices as $service)
                <div class="custom-control custom-checkbox"><input class="custom-control-input service-checkbox" type="checkbox" id="service_{{ $service->id }}" name="services[]" value="{{ $service->id }}" {{ $client->assignedServices->contains($service) ? 'checked' : '' }}><label for="service_{{ $service->id }}" class="custom-control-label">{{ $service->name }}</label></div>
            @empty
                <p class="text-muted">No services have been created yet. <a href="{{ route('services.create') }}">Create one now</a>.</p>
            @endforelse
        </div>
        <button type="button" id="next-to-jobs-btn" class="btn btn-info">Next <i class="fas fa-arrow-right"></i></button>
    </div>

    <div id="job-config-step" style="display: none;">
        <h4>2. Configure Jobs & Tasks</h4>
        <p>Uncheck any jobs or tasks to exclude them. Assign staff members and dates to each task.</p>
        <div id="jobs-accordion-container" class="accordion"></div>
        <hr>
        <button type="button" id="back-to-services-btn" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</button>
        <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Save Assignments</button>
    </div>
</form>