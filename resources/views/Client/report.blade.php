@extends('layouts.app')

@section('title', 'My Reports')
@section('plugins.Select2', true)

@section('css')
    @parent 
    <style>
        .filter-card {
            background-color: #fff;
            border-radius: .375rem; /* Sharper corners */
            box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .report-group {
            background-color: #fff;
            border-radius: .375rem; /* Sharper corners */
            box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .report-header {
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            text-decoration: none !important;
            color: inherit;
        }
        .report-header:hover {
            background-color: #f8f9fa;
        }
        .report-header.service { background-color: #0d6efd; color: white; }
        .report-header.job { background-color: #f8f9fa; border-top: 1px solid #e9ecef; border-bottom: 1px solid #e9ecef; }
        
        .report-title { font-weight: 600; font-size: 1.1rem; margin-bottom: 0; }
        .report-time { font-size: 0.9rem; color: #6c757d; font-weight: 500; }
        .report-header.service .report-time { color: rgba(255,255,255,0.8); }

        .task-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .task-item:last-child { border-bottom: none; }
        .task-icon { color: #6c757d; margin-right: 1rem; }
        .task-details { flex-grow: 1; }
        .task-name { font-weight: 500; }
        .task-meta { font-size: 0.85rem; color: #6c757d; }
        .task-status {
            padding: 0.25em 0.6em;
            font-size: .75em;
            font-weight: 700;
            border-radius: .25rem; /* Sharper status pills */
            text-align: center;
            min-width: 80px;
        }
        .status-to_do { background-color: #f8d7da; color: #721c24; }
        .status-ongoing { background-color: #d1ecf1; color: #0c5460; }
        .status-completed { background-color: #d4edda; color: #155724; }

        .staff-breakdown { background-color: #f8f9fa; border-radius: 4px; border: 1px solid #e9ecef; }
        .collapse-icon { transition: transform 0.3s ease; }
        a[aria-expanded="false"] .collapse-icon { transform: rotate(-180deg); } /* Changed rotation for up/down arrow */

        /* --- NEW STYLES FOR NOTES & COMMENTS MODAL --- */
        #notes-comments-list { max-height: 400px; overflow-y: auto; padding: 5px; }
        .comment-item { display: flex; margin-bottom: 1.25rem; max-width: 85%; animation: fadeInUp 0.4s ease forwards; }
        .comment-item.is-author { margin-left: auto; flex-direction: row-reverse; }
        .comment-author-avatar { flex-shrink: 0; width: 40px; height: 40px; background-color: #6c757d; color: #fff; display: flex; align-items: center; justify-content: center; border-radius: 50%; font-weight: bold; margin: 0 10px; }
        .comment-item.is-author .comment-author-avatar { background-color: #007bff; }
        .comment-body { background-color: #f1f3f5; border-radius: 12px; padding: 0.75rem 1rem; width: 100%; }
        .comment-item.is-author .comment-body { background-color: #e7f5ff; }
        .comment-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem; }
        .comment-author-name { font-weight: 600; font-size: 0.9rem; }
        .comment-timestamp { font-size: 0.75rem; color: #6c757d; }
        .comment-content p { margin: 0; white-space: pre-wrap; word-wrap: break-word; font-size: 0.95rem; }
        .comment-actions { margin-top: 0.5rem; text-align: right; }
        .comment-edit-form { display: none; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>My Reports</h1>
        <button class="btn btn-primary d-print-none" onclick="window.print();"><i class="fas fa-print"></i> Print Report</button>
    </div>
@stop

@section('content')
<div class="filter-card d-print-none">
    <div class="row align-items-center">
        <div class="col-md-9">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" id="search-input" class="form-control" placeholder="Search by Service, Job, or Task..." value="{{ $search ?? '' }}">
                </div>
                <div class="col-md-3">
                    <select id="status-filter" class="form-control" multiple="multiple"></select>
                </div>
                <div class="col-md-5">
                    <div class="d-flex align-items-center">
                        <div id="dropdown-filters" class="row flex-grow-1">
                            <div class="col"><select id="year-filter" class="form-control">@foreach($years as $year)<option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>@endforeach</select></div>
                            <div class="col"><select id="month-filter" class="form-control">@foreach($months as $num => $name)<option value="{{ $num }}" {{ $num == $currentMonth ? 'selected' : '' }}>{{ $name }}</option>@endforeach</select></div>
                        </div>
                        <div id="custom-range-filters" class="row flex-grow-1" style="display: none;">
                            <div class="col"><input type="date" id="start-date-filter" class="form-control" value="{{ $startDate->format('Y-m-d') }}"></div>
                            <div class="col"><input type="date" id="end-date-filter" class="form-control" value="{{ $endDate->format('Y-m-d') }}"></div>
                        </div>
                        <div class="custom-control custom-switch ml-3">
                            <input type="checkbox" class="custom-control-input" id="custom-range-switch" {{ $use_custom_range ? 'checked' : '' }}>
                            <label class="custom-control-label" for="custom-range-switch">Custom</label>
                        </div>
                         <button class="btn btn-secondary ml-3" id="reset-filters">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="client-report-table-container">
    @include('Client._report_table', ['groupedTasks' => $groupedTasks])
</div>

<!-- --- NEW COMMENTS MODAL --- -->
<div class="modal fade" id="task-comments-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comments</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Task: <strong id="modal-task-name"></strong></p>
                <hr>
                <div id="notes-comments-list" class="mb-3">
                    {{-- Content will be loaded here via AJAX --}}
                </div>
                <div id="note-comment-spinner" class="text-center" style="display: none;">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
            <div class="modal-footer" style="display: block;">
                <form id="add-comment-form">
                    <div class="form-group">
                        <textarea name="content" class="form-control" rows="3" placeholder="Add a new comment..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    let debounceTimer;
    let currentAssignedTaskId, currentTaskName;
    const currentUserId = {{ Auth::id() }};

    $('#status-filter').select2({
        placeholder: 'Filter by Status: All',
        data: [
            { id: 'to_do', text: 'To Do' },
            { id: 'ongoing', text: 'Ongoing' },
            { id: 'completed', text: 'Completed' }
        ]
    }).val({!! json_encode($statuses) !!}).trigger('change');

    function fetch_report_data() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            $('#client-report-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
            let data = {
                search: $('#search-input').val(),
                statuses: $('#status-filter').val(),
                use_custom_range: $('#custom-range-switch').is(':checked').toString(),
                start_date: $('#start-date-filter').val(),
                end_date: $('#end-date-filter').val(),
                year: $('#year-filter').val(),
                month: $('#month-filter').val()
            };
            $.ajax({
                url: "{{ route('client.reports.index') }}",
                data: data,
                success: (response) => $('#client-report-table-container').html(response),
                error: () => $('#client-report-table-container').html('<p class="text-danger text-center">Failed to load data.</p>')
            });
        }, 500);
    }

    function toggleDateFilters(useCustom) {
        if (useCustom) {
            $('#dropdown-filters').hide();
            $('#custom-range-filters').show();
        } else {
            $('#dropdown-filters').show();
            $('#custom-range-filters').hide();
        }
    }

    $('#custom-range-switch').on('change', function() {
        toggleDateFilters(this.checked);
        fetch_report_data();
    });
    
    $('#reset-filters').on('click', function() {
        const today = new Date();
        $('#search-input').val('');
        $('#status-filter').val(null).trigger('change.select2');
        $('#custom-range-switch').prop('checked', false).trigger('change');
        $('#year-filter').val(today.getFullYear());
        $('#month-filter').val(today.getMonth() + 1);
        fetch_report_data();
    });

    // Initial state
    toggleDateFilters($('#custom-range-switch').is(':checked'));

    $('#search-input, #status-filter, #year-filter, #month-filter, #start-date-filter, #end-date-filter').on('keyup change', fetch_report_data);

    // --- NEW SCRIPT FOR COMMENTS MODAL ---
    function loadComments() {
        const list = $('#notes-comments-list');
        const spinner = $('#note-comment-spinner');
        list.empty();
        spinner.show();

        const url = `/tasks/${currentAssignedTaskId}/comments`;

        $.get(url, function(data) {
            if (data.length === 0) {
                list.html('<p class="text-center text-muted">No comments to show.</p>');
            } else {
                data.forEach(item => list.append(renderComment(item)));
            }
        }).always(() => spinner.hide());
    }

    function renderComment(item) {
        const isAuthor = item.author.id === currentUserId;
        const authorName = isAuthor ? 'You' : item.author.name.split(' ')[0];
        const authorInitials = authorName.substring(0, 2).toUpperCase();
        const authorBadge = item.author.type === 'C' ? '' : '<span class="badge badge-info ml-2">Staff</span>';

        const actions = isAuthor ? `
            <div class="comment-actions">
                <button class="btn btn-xs btn-link text-muted edit-comment-btn">Edit</button>
                <button class="btn btn-xs btn-link text-danger delete-comment-btn">Delete</button>
            </div>
        ` : '';

        return `
            <div class="comment-item ${isAuthor ? 'is-author' : ''}" data-id="${item.id}">
                <div class="comment-author-avatar">${authorInitials}</div>
                <div class="comment-body">
                    <div class="comment-meta">
                        <span class="comment-author-name">${authorName}${authorBadge}</span>
                        <span class="comment-timestamp">${new Date(item.created_at).toLocaleString()}</span>
                    </div>
                    <div class="comment-content"><p>${item.content}</p></div>
                    <div class="comment-edit-form">
                        <textarea class="form-control" rows="3">${item.content}</textarea>
                        <div class="mt-2 text-right">
                            <button class="btn btn-xs btn-secondary cancel-edit-btn">Cancel</button>
                            <button class="btn btn-xs btn-primary save-edit-btn">Save</button>
                        </div>
                    </div>
                    ${actions}
                </div>
            </div>
        `;
    }

    $(document).on('click', '.open-comments-modal', function() {
        const button = $(this);
        currentAssignedTaskId = button.data('task-id');
        currentTaskName = button.data('task-name');

        const modal = $('#task-comments-modal');
        modal.find('#modal-task-name').text(currentTaskName);

        loadComments();
        modal.modal('show');
    });

    $('#add-comment-form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const content = form.find('textarea[name="content"]').val();
        if (!content) return;

        $.post(`/tasks/${currentAssignedTaskId}/comments`, { content }, function(newItem) {
            loadComments(); // Refresh the list
            form[0].reset();
        });
    });

    $(document).on('click', '.edit-comment-btn', function() {
        const itemDiv = $(this).closest('.comment-item');
        itemDiv.find('.comment-content').hide();
        itemDiv.find('.comment-actions').hide();
        itemDiv.find('.comment-edit-form').show();
    });
    
    $(document).on('click', '.cancel-edit-btn', function() {
        const itemDiv = $(this).closest('.comment-item');
        const originalContent = itemDiv.find('.comment-content p').text();
        itemDiv.find('.comment-edit-form textarea').val(originalContent);
        itemDiv.find('.comment-edit-form').hide();
        itemDiv.find('.comment-content').show();
        itemDiv.find('.comment-actions').show();
    });

    $(document).on('click', '.save-edit-btn', function() {
        const itemDiv = $(this).closest('.comment-item');
        const itemId = itemDiv.data('id');
        const content = itemDiv.find('textarea').val();
        
        $.ajax({
            url: `/comments/${itemId}`,
            method: 'PUT',
            data: { content },
            success: function(updatedItem) {
                loadComments(); // Refresh the list
            }
        });
    });

    $(document).on('click', '.delete-comment-btn', function() {
        if (!confirm('Are you sure you want to delete this comment?')) return;

        const itemDiv = $(this).closest('.comment-item');
        const itemId = itemDiv.data('id');
        
        $.ajax({
            url: `/comments/${itemId}`,
            method: 'DELETE',
            success: function() {
                itemDiv.fadeOut(300, function() { 
                    $(this).remove(); 
                    const list = $('#notes-comments-list');
                    if (list.children('.comment-item').length === 0) {
                         list.html('<p class="text-center text-muted">No comments to show.</p>');
                    }
                });
            }
        });
    });
});
</script>
@stop

