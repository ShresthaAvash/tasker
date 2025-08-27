<script>
$(document).ready(function() {
    // --- THIS IS THE DEFINITIVE FIX FOR THE MODALS ---
    $('#contactModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var modal = $(this);
        var form = modal.find('form');

        form[0].reset();

        if (action === 'edit') {
            var contact = button.data('contact');
            modal.find('.modal-title').text('Edit Contact');
            form.attr('action', '/organization/client-contacts/' + contact.id);
            modal.find('#contact-method').val('PUT');
            
            modal.find('#contact-name').val(contact.name);
            modal.find('#contact-email').val(contact.email);
            modal.find('#contact-phone').val(contact.phone);
            modal.find('#contact-position').val(contact.position);
        } else {
            modal.find('.modal-title').text('Add New Contact');
            form.attr('action', '{{ route('clients.contacts.store', $client) }}');
            modal.find('#contact-method').val('POST');
        }
    });

    $('#noteModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var modal = $(this);
        var form = modal.find('form');

        form[0].reset();

        if (action === 'edit') {
            var note = button.data('note');
            modal.find('.modal-title').text('Edit Note');
            form.attr('action', '/organization/client-notes/' + note.id);
            modal.find('#note-method').val('PUT');
            
            modal.find('#note-title').val(note.title);
            modal.find('#note-content').val(note.content);
            modal.find('#note-date').val(note.note_date.split(' ')[0]); // Format date to YYYY-MM-DD
        } else {
            modal.find('.modal-title').text('Add New Note');
            form.attr('action', '{{ route('clients.notes.store', $client) }}');
            modal.find('#note-method').val('POST');
            modal.find('#note-date').val(new Date().toISOString().slice(0, 10)); // Default to today
        }
    });
    // --- END OF FIX ---

    let lastTab = sessionStorage.getItem('activeClientTab');
    if (lastTab) {
        $('#client-tabs a[href="' + lastTab + '"]').tab('show');
    }

    $('#client-tabs a[data-toggle="pill"]').on('shown.bs.tab', function(e) {
        sessionStorage.setItem('activeClientTab', $(e.target).attr('href'));
    });

    const serviceSelectionStep = $('#service-selection-step');
    const jobConfigStep = $('#job-config-step');
    const jobsContainer = $('#jobs-accordion-container');
    const allStaffData = {!! $allStaffJson !!};
    const clientTasks = {!! json_encode($client->assignedTasks->keyBy('task_template_id')) !!};
    const originallyAssignedServiceIds = {!! json_encode($client->assignedServices->pluck('id')) !!};
    let checkboxToUnassign = null;

    // --- START OF MODIFICATIONS FOR REQUIREMENTS ---

    // Function to update the "Select All" checkbox based on individual checkbox states
    function updateSelectAllState() {
        const allServiceCheckboxes = $('.service-checkbox');
        const checkedServiceCheckboxes = $('.service-checkbox:checked');
        const allCount = allServiceCheckboxes.length;
        const checkedCount = checkedServiceCheckboxes.length;

        if (allCount > 0 && allCount === checkedCount) {
            $('#select-all-services').prop('checked', true).prop('indeterminate', false);
        } else if (checkedCount > 0) {
            $('#select-all-services').prop('checked', false).prop('indeterminate', true);
        } else {
            $('#select-all-services').prop('checked', false).prop('indeterminate', false);
        }
    }

    // Check previously assigned services on page load
    originallyAssignedServiceIds.forEach(serviceId => {
        $(`#service_${serviceId}`).prop('checked', true);
    });
    updateSelectAllState(); // Update select-all based on initial state

    // "Select All" functionality
    $('#select-all-services').on('change', function() {
        $('.service-checkbox').prop('checked', $(this).is(':checked'));
    });
    
    // Update "Select All" when individual services are toggled
    $('#service-checkbox-list').on('change', '.service-checkbox', function() {
        updateSelectAllState();
    });

    // --- END OF MODIFICATIONS FOR REQUIREMENTS ---

    function renderJobsAndTasks(jobs) {
        jobsContainer.empty();
        if (!jobs || jobs.length === 0) {
            jobsContainer.html('<p class="text-muted text-center p-3">The selected services do not have any jobs or tasks configured.</p>');
            return;
        }

        const jobsByService = jobs.reduce((acc, job) => {
            const serviceId = job.service.id;
            if (!acc[serviceId]) { acc[serviceId] = { name: job.service.name, jobs: [] }; }
            acc[serviceId].jobs.push(job);
            return acc;
        }, {});

        Object.entries(jobsByService).forEach(([serviceId, serviceGroup]) => {
            let jobsHtml = '';
            serviceGroup.jobs.forEach(job => {
                let taskHtml = '';
                if (job.tasks && job.tasks.length > 0) {
                    job.tasks.forEach(task => {
                        const assignedTask = clientTasks[task.id];
                        const isAlreadyAssigned = clientTasks.hasOwnProperty(task.id);
                        
                        const isChecked = isAlreadyAssigned ? 'checked' : '';

                        const assignedStaffIds = assignedTask ? assignedTask.staff.map(s => s.id) : [];

                        const assignedStartDate = (assignedTask && assignedTask.start) ? assignedTask.start.slice(0, 16).replace(' ', 'T') : (task.start ? task.start.slice(0, 16).replace(' ', 'T') : '');
                        const assignedEndDate = (assignedTask && assignedTask.end) ? assignedTask.end.slice(0, 16).replace(' ', 'T') : (task.end ? task.end.slice(0, 16).replace(' ', 'T') : '');

                        const isStartDateRequired = task.start === null;
                        const startDateLabel = isStartDateRequired ? 'Start Date (Required)' : 'Start Date';

                        const startDateInputHtml = `<div style="width: 210px;"><label class="d-block small text-muted mb-0">${startDateLabel}</label><input type="datetime-local" class="form-control form-control-sm task-start-date" name="task_start_dates[${task.id}]" value="${assignedStartDate}" data-is-required="${isStartDateRequired}"></div>`;
                        const endDateInputHtml = `<div style="width: 210px;"><label class="d-block small text-muted mb-0">End Date (Optional)</label><input type="datetime-local" class="form-control form-control-sm" name="task_end_dates[${task.id}]" value="${assignedEndDate}"></div>`;
                        
                        let taskNameHtml = task.name;
                        if (isAlreadyAssigned) {
                            taskNameHtml += ` <small class="text-danger ml-2"> (Already assigned. Unchecking it will remove the assignment )</small>`;
                        }

                        taskHtml += `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <div class="custom-control custom-checkbox" style="min-width: 250px; flex: 1;">
                                        <input type="checkbox" class="custom-control-input task-checkbox" name="tasks[${task.id}]" id="task_${task.id}" ${isChecked} data-job-id="${job.id}" data-service-id="${job.service_id}" data-is-assigned="${isAlreadyAssigned}">
                                        <label class="custom-control-label font-weight-normal" for="task_${task.id}">${taskNameHtml}</label>
                                    </div>
                                    <div class="task-inputs-wrapper d-flex align-items-center" style="gap: 15px;">
                                        ${startDateInputHtml} ${endDateInputHtml}
                                        <div style="width: 300px;"><label class="d-block small text-muted mb-0">Assigned Staff</label><select class="form-control staff-select" name="staff_assignments[${task.id}][]" multiple="multiple" style="width: 100%;" data-assigned-staff='${JSON.stringify(assignedStaffIds)}'></select></div>
                                    </div>
                                </div>
                            </li>`;
                    });
                } else { taskHtml = '<li class="list-group-item text-muted">No tasks in this job.</li>'; }
                
                jobsHtml += `<div class="card mb-2"><a href="#collapse_job_${job.id}" class="card-header job-header-link bg-light d-flex align-items-center text-dark font-weight-bold" data-toggle="collapse" aria-expanded="true" style="text-decoration: none; padding: 0.75rem 1.25rem;"><div class="custom-control custom-checkbox d-inline-block mr-3" onclick="event.stopPropagation();"><input type="checkbox" class="custom-control-input job-master-checkbox" id="job_master_${job.id}" data-job-id="${job.id}" data-service-id="${job.service_id}"><label class="custom-control-label" for="job_master_${job.id}">&nbsp;</label></div><span class="flex-grow-1">${job.name}</span><i class="fas fa-chevron-down collapse-icon"></i></a><div id="collapse_job_${job.id}" class="collapse show"><ul class="list-group list-group-flush">${taskHtml}</ul></div></div>`;
            });

            const serviceHtml = `<div class="card mb-3 shadow-sm"><a href="#collapse_service_${serviceId}" class="card-header service-header-link d-flex align-items-center text-dark font-weight-bold" data-toggle="collapse" aria-expanded="true" style="background-color: #e3f2fd; text-decoration: none; padding: 1rem 1.25rem;"><div class="custom-control custom-checkbox d-inline-block mr-3" onclick="event.stopPropagation();"><input type="checkbox" class="custom-control-input service-master-checkbox" id="service_master_${serviceId}" data-service-id="${serviceId}"><label class="custom-control-label" for="service_master_${serviceId}">&nbsp;</label></div><span class="flex-grow-1" style="font-size: 1.2rem;">Service: ${serviceGroup.name}</span><i class="fas fa-chevron-down collapse-icon"></i></a><div id="collapse_service_${serviceId}" class="collapse show"><div class="card-body">${jobsHtml}</div></div></div>`;
            jobsContainer.append(serviceHtml);
        });
        
        $('[data-toggle="tooltip"]').tooltip();
        $('.staff-select').each(function() { $(this).select2({ placeholder: 'Assign Staff', data: allStaffData, width: '100%' }).val(JSON.parse($(this).attr('data-assigned-staff') || '[]')).trigger('change'); });
        
        $('.task-checkbox').each(function() { 
            toggleRequiredForDate(this); 
            toggleRequiredForStaff(this);
        });

        $('.job-master-checkbox, .service-master-checkbox').each(function() { updateParentCheckboxState($(this)); });
        jobsContainer.on('click', 'a[data-toggle="collapse"]', function() { $(this).attr('aria-expanded', $(this).attr('aria-expanded') !== 'true'); });
    }
    
    $('#next-to-jobs-btn').on('click', function() {
        const selectedServiceIds = $('.service-checkbox:checked').map((_, el) => $(el).val()).get();
        jobsContainer.html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        serviceSelectionStep.hide(); jobConfigStep.show();
        if (selectedServiceIds.length === 0) { renderJobsAndTasks([]); return; }
        $.ajax({ url: '{{ route('clients.services.getJobs') }}', method: 'GET', data: { service_ids: selectedServiceIds }, success: jobs => renderJobsAndTasks(jobs), error: () => jobsContainer.html('<p class="text-danger text-center p-3">Failed to load job data.</p>') });
    });

    $('#back-to-services-btn').on('click', () => { jobConfigStep.hide(); serviceSelectionStep.show(); });

    function updateParentCheckboxState(checkbox) {
        const isService = checkbox.hasClass('service-master-checkbox');
        const scope = isService ? checkbox.closest('.card') : checkbox.closest('.card').find('.collapse');
        const childSelector = isService ? '.job-master-checkbox' : '.task-checkbox';
        const children = scope.find(childSelector);
        const checkedChildren = children.filter(':checked');
        checkbox.prop('checked', children.length > 0 && children.length === checkedChildren.length);
        checkbox.prop('indeterminate', checkedChildren.length > 0 && checkedChildren.length < children.length);
    }
    
    // --- THIS IS THE DEFINITIVE FIX FOR ISSUE #2 ---
    function handleUncheck(e) {
        const $checkbox = $(this);
        if ($checkbox.is(':checked')) {
            // This logic runs when checking a box, which doesn't need a warning.
            return;
        }

        // This logic runs when unchecking a box.
        let hasAssigned = false;
        if ($checkbox.hasClass('task-checkbox')) {
            if ($checkbox.attr('data-is-assigned') === 'true') {
                hasAssigned = true;
            }
        } else if ($checkbox.hasClass('job-master-checkbox')) {
            $(`.task-checkbox[data-job-id="${$checkbox.data('job-id')}"]`).each(function() {
                if ($(this).attr('data-is-assigned') === 'true') {
                    hasAssigned = true;
                    return false; // Exit the .each() loop
                }
            });
        } else if ($checkbox.hasClass('service-master-checkbox')) {
            $(`.task-checkbox[data-service-id="${$checkbox.data('service-id')}"]`).each(function() {
                if ($(this).attr('data-is-assigned') === 'true') {
                    hasAssigned = true;
                    return false; // Exit the .each() loop
                }
            });
        }

        if (hasAssigned) {
            e.preventDefault(); // Stop the unchecking action immediately
            checkboxToUnassign = this; // Store a reference to the clicked checkbox
            $('#unassign-task-warning-modal').modal('show');
        }
    }

    $('#confirm-unassign-btn').on('click', function() {
        if (!checkboxToUnassign) return;
        const $checkbox = $(checkboxToUnassign);
        
        // Temporarily remove the click listener to prevent an infinite loop
        $checkbox.off('click', handleUncheck);

        // Manually uncheck the box and trigger the 'change' handler to update children
        $checkbox.prop('checked', false).trigger('change');

        // Re-attach the click listener for future interactions
        $checkbox.on('click', handleUncheck);

        $('#unassign-task-warning-modal').modal('hide');
        checkboxToUnassign = null;
    });

    const changeHandler = function(e) {
        const $checkbox = $(this);
        if ($checkbox.hasClass('task-checkbox')) {
            toggleRequiredForDate(this);
            toggleRequiredForStaff(this);
            updateParentCheckboxState($(`#job_master_${$checkbox.data('job-id')}`));
            updateParentCheckboxState($(`#service_master_${$checkbox.data('service-id')}`));
        } else if ($checkbox.hasClass('job-master-checkbox')) {
            const isChecked = $checkbox.is(':checked');
            $(`.task-checkbox[data-job-id="${$checkbox.data('job-id')}"]`).prop('checked', isChecked).trigger('change');
        } else if ($checkbox.hasClass('service-master-checkbox')) {
            const isChecked = $checkbox.is(':checked');
            const serviceId = $checkbox.data('service-id');
            $(`.job-master-checkbox[data-service-id="${serviceId}"]`).prop('checked', isChecked).prop('indeterminate', false);
            $(`.task-checkbox[data-service-id="${serviceId}"]`).prop('checked', isChecked).trigger('change');
        }
    };
    
    // MODIFIED: Use 'click' for handleUncheck to prevent the state change before confirmation
    jobsContainer.on('click', '.task-checkbox, .job-master-checkbox, .service-master-checkbox', handleUncheck);
    jobsContainer.on('change', '.task-checkbox, .job-master-checkbox, .service-master-checkbox', changeHandler);

    // --- END OF FIX ---

    function toggleRequiredForDate(checkbox) {
        const isChecked = $(checkbox).is(':checked');
        const listItem = $(checkbox).closest('li.list-group-item');
        const startDateInput = listItem.find('input.task-start-date');
        if (startDateInput.length > 0 && startDateInput.data('is-required')) {
            startDateInput.prop('required', isChecked);
        }
    }

    function toggleRequiredForStaff(checkbox) {
        const isChecked = $(checkbox).is(':checked');
        const listItem = $(checkbox).closest('li.list-group-item');
        const staffSelect = listItem.find('select.staff-select');
        if (staffSelect.length > 0) {
            staffSelect.prop('required', isChecked);
        }
    }
    
    let jobCounter = 0;
    
    $('#add-new-job-btn').on('click', function() {
        if ($('#service-jobs-container .text-muted').length) $('#service-jobs-container').empty();
        jobCounter++;
        const jobTemplate = $('#job-template .job-block').clone();
        jobTemplate.attr('data-job-id', jobCounter);
        jobTemplate.find('.add-task-to-job-btn').data('job-id', jobCounter);
        $('#service-jobs-container').append(jobTemplate);
    });
    
    $('#service-jobs-container').on('click', '.remove-job-btn', function() {
        $(this).closest('.job-block').remove();
        if ($('#service-jobs-container').children().length === 0) {
            $('#service-jobs-container').html('<p class="text-muted text-center">No jobs added yet.</p>');
        }
    });

    $('#service-jobs-container').on('click', '.add-task-to-job-btn', function() {
        const jobId = $(this).data('job-id');
        const taskModal = $('#taskModal');
        taskModal.data('target-job-id', jobId);
        taskModal.modal('show');
    });

    $('#taskModal').on('show.bs.modal', function (event) {
        const form = $(this).find('form');
        form[0].reset();

        function toggleRecurringFields() {
            if ($('#is_recurring').is(':checked')) {
                $('#recurring-options').slideDown();
                $('#task-end').prop('required', false);
            } else {
                $('#recurring-options').slideUp();
                $('#task-end').prop('required', false);
            }
        }
        
        $('#is_recurring').off('change').on('change', toggleRecurringFields);
        toggleRecurringFields();
    });

    $('#taskModal form').on('submit', function(e) {
        const targetJobId = $('#taskModal').data('target-job-id');
        if (!targetJobId) return;
        
        e.preventDefault();
        const form = $(this);
        const taskData = {
            name: form.find('[name="name"]').val(), description: form.find('[name="description"]').val(),
            start: form.find('[name="start"]').val(), end: form.find('[name="end"]').val(),
            is_recurring: form.find('[name="is_recurring"]').is(':checked'),
            recurring_frequency: form.find('[name="recurring_frequency"]').val(),
            staff_designation_id: form.find('[name="staff_designation_id"]').val()
        };
        const taskHtml = `<div class="task-item" data-task-data='${JSON.stringify(taskData)}'><span>${taskData.name}</span><button type="button" class="btn btn-xs btn-danger float-right remove-task-btn"><i class="fas fa-times"></i></button></div>`;
        
        $(`.job-block[data-job-id="${targetJobId}"]`).find('.task-list').append(taskHtml);
        
        $('#taskModal').modal('hide').removeData('target-job-id');
        form[0].reset();
    });
    
    $('#service-jobs-container').on('click', '.remove-task-btn', function() {
        $(this).closest('.task-item').remove();
    });

    $('#save-new-service-btn').on('click', function() {
        const button = $(this);
        const feedback = $('#service-creation-feedback');
        const serviceData = { name: $('#new-service-name').val(), description: $('#new-service-description').val(), jobs: [] };
        $('#service-jobs-container .job-block').each(function() {
            const jobBlock = $(this);
            const jobData = { name: jobBlock.find('.job-name-input').val(), tasks: [] };
            jobBlock.find('.task-item').each(function() { jobData.tasks.push($(this).data('task-data')); });
            serviceData.jobs.push(jobData);
        });
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        feedback.hide().removeClass('alert-success alert-danger');
        $.ajax({
            url: '{{ route("clients.services.storeForClient", $client) }}',
            method: 'POST', data: JSON.stringify(serviceData), contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                const newCheckbox = `<div class="custom-control custom-checkbox"><input class="custom-control-input service-checkbox" type="checkbox" id="service_${response.service.id}" name="services[]" value="${response.service.id}" checked><label for="service_${response.service.id}" class="custom-control-label">${response.service.name}</label></div>`;
                $('#service-checkbox-list').append(newCheckbox);
                $('#createServiceModal').modal('hide');
            },
            error: function(xhr) {
                let errorMsg = 'An unknown error occurred.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join(' ');
                }
                feedback.addClass('alert-danger').text(errorMsg).fadeIn();
            },
            complete: function() {
                button.prop('disabled', false).text('Save Service and Assign');
            }
        });
    });

    $(document).on('show.bs.modal', '.modal', function () {
        const zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(() => {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });
    $(document).on('hidden.bs.modal', '.modal', function () {
        if ($('.modal:visible').length > 0) {
            setTimeout(() => {
                $(document.body).addClass('modal-open');
            }, 0);
        }
    });

    const uploadModal = $('#documentUploadModal');
    const dropZone = uploadModal.find('.file-drop-zone');
    const fileInput = uploadModal.find('#document_file');
    const filePreviewList = uploadModal.find('#file-preview-list');
    const uploadButton = uploadModal.find('button[type="submit"]');
    let fileStore = new DataTransfer();

    function handleFiles(files) {
        for (const file of files) { fileStore.items.add(file); }
        updateFileInput(); renderPreviews();
    }

    function renderPreviews() {
        filePreviewList.empty();
        uploadButton.prop('disabled', fileStore.files.length === 0);
        if (fileStore.files.length === 0) { filePreviewList.hide(); return; }
        filePreviewList.show();
        for (const file of fileStore.files) {
            const fileType = file.name.split('.').pop().toLowerCase();
            let iconClass = 'fas fa-file-alt', iconColor = 'text-muted';
            if (file.type.startsWith('image/')) { iconClass = 'fas fa-file-image'; iconColor = 'text-info'; }
            else if (fileType === 'pdf') { iconClass = 'fas fa-file-pdf'; iconColor = 'text-danger'; }
            else if (['doc', 'docx'].includes(fileType)) { iconClass = 'fas fa-file-word'; iconColor = 'text-primary'; }
            else if (['xls', 'xlsx'].includes(fileType)) { iconClass = 'fas fa-file-excel'; iconColor = 'text-success'; }
            const previewHtml = `<div class="file-preview-item" data-name="${file.name}"><i class="file-preview-icon ${iconClass} ${iconColor}"></i><div class="file-preview-info"><div class="file-preview-name">${file.name}</div><div class="file-preview-size">${(file.size / 1024).toFixed(1)} KB</div></div><button type="button" class="remove-file-btn">&times;</button></div>`;
            filePreviewList.append(previewHtml);
        }
    }
    function updateFileInput() { fileInput.prop('files', fileStore.files); }
    dropZone.on('click', () => fileInput.click());
    dropZone.on('dragover', (e) => { e.preventDefault(); e.stopPropagation(); dropZone.addClass('is-active'); });
    dropZone.on('dragleave', (e) => { e.preventDefault(); e.stopPropagation(); dropZone.removeClass('is-active'); });
    dropZone.on('drop', (e) => { e.preventDefault(); e.stopPropagation(); dropZone.removeClass('is-active'); handleFiles(e.originalEvent.dataTransfer.files); });
    fileInput.on('change', function() { handleFiles(this.files); });
    filePreviewList.on('click', '.remove-file-btn', function() {
        const itemToRemove = $(this).closest('.file-preview-item');
        const fileName = itemToRemove.data('name');
        itemToRemove.addClass('removing');
        setTimeout(() => {
            const newFiles = new DataTransfer();
            for (const file of fileStore.files) {
                if (file.name !== fileName) { newFiles.items.add(file); }
            }
            fileStore = newFiles;
            updateFileInput();
            itemToRemove.remove();
            if (fileStore.files.length === 0) {
                filePreviewList.hide();
                uploadButton.prop('disabled', true);
            }
        }, 300);
    });
    uploadModal.on('hidden.bs.modal', function () {
        fileStore = new DataTransfer();
        updateFileInput();
        renderPreviews();
        $(this).find('form')[0].reset();
    });
});
</script>