@extends('layouts.app')

@section('title', 'Contact Messages')

@section('content_header')
    <h1>Contact Messages</h1>
@stop

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Inbox</h3>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-12">
                <input type="text" id="search-input" class="form-control" placeholder="Search by name, email, or company...">
            </div>
        </div>

        <div id="messages-table-container">
            @include('SuperAdmin.messages._messages_table', ['messages' => $messages])
        </div>
    </div>
</div>

<!-- Message Detail Modal -->
<div class="modal fade" id="messageDetailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="message-subject"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>From:</strong> <span id="message-from"></span></p>
                <p><strong>Email:</strong> <a href="#" id="message-email"></a></p>
                <p><strong>Company:</strong> <span id="message-company"></span></p>
                <p><strong>Received:</strong> <span id="message-date"></span></p>
                <hr>
                <div id="message-body" style="white-space: pre-wrap;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    function fetch_messages(page = 1) {
        clearTimeout(debounceTimer);
        const search = $('#search-input').val();
        $('#messages-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        
        $.ajax({
            url: "{{ route('superadmin.messages.index') }}",
            data: { page: page, search: search },
            success: function(data) {
                $('#messages-table-container').html(data);
            },
            error: function() {
                $('#messages-table-container').html('<p class="text-danger">Failed to load messages.</p>');
            }
        });
    }

    $('#search-input').on('keyup', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            fetch_messages(1);
        }, 300);
    });

    $(document).on('click', '#messages-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        fetch_messages(page);
    });

    $(document).on('click', '.view-message-btn', function() {
        const messageId = $(this).data('id');
        const row = $(this).closest('tr');

        $.ajax({
            url: `/superadmin/messages/${messageId}`,
            type: 'GET',
            success: function(message) {
                $('#message-subject').text(`Message from ${message.first_name} ${message.last_name}`);
                $('#message-from').text(`${message.first_name} ${message.last_name}`);
                $('#message-email').text(message.email).attr('href', `mailto:${message.email}`);
                $('#message-company').text(message.company || 'N/A');
                $('#message-date').text(new Date(message.created_at).toLocaleString());
                $('#message-body').text(message.message);

                $('#messageDetailModal').modal('show');
                
                // Mark as read in the UI
                row.removeClass('font-weight-bold');
                row.find('.badge-success').remove();
            }
        });
    });
});
</script>
@stop