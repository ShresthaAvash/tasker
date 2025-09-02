@extends('layouts.app')

@section('title', 'Clients')
@section('plugins.Select2', true)

@section('content_header')
    <h1>Clients</h1>
@stop

@section('content')
<div id="action-bar" class="mb-3" style="display: none;">
    <button id="show-message-modal-btn" class="btn btn-primary" data-toggle="modal" data-target="#messageModal">
        <i class="fas fa-paper-plane"></i> Send Message to <span id="selected-count">0</span> Client(s)
    </button>
</div>

<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">All Clients</h3>
        <div class="card-tools">
            <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">Add New Client</a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif
        
        <div class="row mb-3">
            <div class="col-md-8">
                <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="statuses[]" id="status-filter" class="form-control" multiple="multiple">
                    <option value="A">Active</option>
                    <option value="I">Inactive</option>
                </select>
            </div>
        </div>

        <div id="clients-table-container">
            @include('Organization.clients._clients_table', ['clients' => $clients, 'sort_by' => $sort_by, 'sort_order' => $sort_order])
        </div>
    </div>
</div>

<div class="modal fade" id="messageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('clients.sendMessage') }}" method="POST">
                @csrf
                <div id="modal-hidden-inputs"></div>
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Send Message</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><strong>Sending to:</strong></label>
                        <p id="recipient-list" class="text-muted"></p>
                    </div>
                    <div class="form-group">
                        <label for="subject"><strong>Subject</strong></label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="message"><strong>Message</strong></label>
                        <textarea name="message" class="form-control" rows="5" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;
    let selectedClients = new Map();

    $('#status-filter').select2({
        placeholder: 'Filter by Status (All)'
    });

    function fetch_clients_data(page, sort_by, sort_order, search, statuses) {
        $('#clients-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');

        $.ajax({
            url: "{{ route('clients.index') }}",
            data: { page, sort_by, sort_order, search, statuses },
            success: function(data) {
                $('#clients-table-container').html(data);
                updateMessagingUI();
            },
            error: function() {
                $('#clients-table-container').html('<p class="text-danger text-center">Failed to load data.</p>');
            }
        });
    }

    function trigger_fetch() {
        const search = $('#search-input').val();
        const statuses = $('#status-filter').val();
        const sort_by = $('#sort_by').val() || 'created_at';
        const sort_order = $('#sort_order').val() || 'desc';
        fetch_clients_data(1, sort_by, sort_order, search, statuses);
    }

    function trigger_fetch_debounced() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(trigger_fetch, 300);
    }
    
    function updateMessagingUI() {
        $('.client-checkbox').each(function() {
            const clientId = $(this).data('id');
            $(this).prop('checked', selectedClients.has(clientId));
        });
        updateMasterCheckbox();
        const selectedCount = selectedClients.size;
        $('#selected-count').text(selectedCount);
        $('#action-bar').toggle(selectedCount > 0);
    }

    function updateMasterCheckbox() {
        const totalCheckboxes = $('.client-checkbox').length;
        const checkedCheckboxes = $('.client-checkbox:checked').length;
        $('#master-checkbox').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
        $('#master-checkbox').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
    }
    
    $('#search-input').on('keyup', trigger_fetch_debounced);
    $('#status-filter').on('change', trigger_fetch);

    $(document).on('click', '#clients-table-container .sort-link', function(e) {
        e.preventDefault();
        $('#sort_by').val($(this).data('sortby'));
        $('#sort_order').val($(this).data('sortorder'));
        trigger_fetch();
    });

    $(document).on('click', '#clients-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = new URLSearchParams($(this).attr('href').split('?')[1]).get('page');
        fetch_clients_data(page, $('#sort_by').val(), $('#sort_order').val(), $('#search-input').val(), $('#status-filter').val());
    });
    
    $(document).on('change', '#master-checkbox', function() {
        const isChecked = $(this).is(':checked');
        $('.client-checkbox').each(function() {
            $(this).prop('checked', isChecked).trigger('change');
        });
    });

    $(document).on('change', '.client-checkbox', function() {
        const clientId = $(this).data('id');
        const clientName = $(this).data('name');
        if (this.checked) {
            selectedClients.set(clientId, clientName);
        } else {
            selectedClients.delete(clientId);
        }
        updateMessagingUI();
    });
    
    $('#show-message-modal-btn').on('click', function() {
        $('#recipient-list').text(Array.from(selectedClients.values()).join(', '));
        $('#modal-hidden-inputs').empty();
        selectedClients.forEach((name, id) => {
            $('#modal-hidden-inputs').append(`<input type="hidden" name="client_ids[]" value="${id}">`);
        });
    });

    setTimeout(() => $('#success-alert').fadeOut('slow'), 5000);
});
</script>
@stop