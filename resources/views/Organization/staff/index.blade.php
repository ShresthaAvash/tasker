@extends('layouts.app')

@section('title', 'Staff')

@section('content_header')
    <h1>Staff Members</h1>
@stop

@section('content')
{{-- --- THIS IS THE FIX --- --}}
{{-- We add 'card-info' and 'card-outline' to style the card --}}
<div class="card card-info card-outline">
    <div class="card-header">
        <h3 class="card-title">All Staff</h3>
        <div class="card-tools">
            {{-- --- THIS IS THE FIX --- --}}
            {{-- We change the button to 'btn-info' to match the theme --}}
            <a href="{{ route('staff.create') }}" class="btn btn-info btn-sm">Add New Staff</a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success" id="success-alert">{{ session('success') }}</div>
        @endif
        
        <!-- Filter and Search Row -->
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" name="search" id="search-input" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="designation_id" id="designation-filter" class="form-control">
                    <option value="">All Designations</option>
                    @foreach($designations as $designation)
                        <option value="{{ $designation->id }}" {{ request('designation_id') == $designation->id ? 'selected' : '' }}>
                            {{ $designation->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- This container will be updated by AJAX -->
        <div id="staff-table-container">
            @include('Organization.staff._staff_table', ['staff' => $staff, 'sort_by' => $sort_by, 'sort_order' => $sort_order])
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    let debounceTimer;

    function fetch_staff_data(page, sort_by, sort_order, search, designation_id) {
        $('#staff-table-container').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
        $.ajax({
            url: "{{ route('staff.index') }}",
            data: { page: page, sort_by: sort_by, sort_order: sort_order, search: search, designation_id: designation_id },
            success: function(data) { $('#staff-table-container').html(data); },
            error: function() {
                alert('Could not load staff data. Please refresh the page.');
                $('#staff-table-container').html('<p class="text-danger text-center">Failed to load data.</p>');
            }
        });
    }

    $('#search-input').on('keyup', function() {
        clearTimeout(debounceTimer);
        const search = $(this).val();
        debounceTimer = setTimeout(function() {
            const sort_by = $('#sort_by').val();
            const sort_order = $('#sort_order').val();
            const designation_id = $('#designation-filter').val();
            fetch_staff_data(1, sort_by, sort_order, search, designation_id);
        }, 300);
    });

    $('#designation-filter').on('change', function() {
        const designation_id = $(this).val();
        const search = $('#search-input').val();
        const sort_by = $('#sort_by').val();
        const sort_order = $('#sort_order').val();
        fetch_staff_data(1, sort_by, sort_order, search, designation_id);
    });

    $(document).on('click', '#staff-table-container .sort-link', function(e) {
        e.preventDefault();
        const sort_by = $(this).data('sortby');
        const sort_order = $(this).data('sortorder');
        const search = $('#search-input').val();
        const designation_id = $('#designation-filter').val();
        fetch_staff_data(1, sort_by, sort_order, search, designation_id);
    });

    $(document).on('click', '#staff-table-container .pagination a', function(e) {
        e.preventDefault();
        const page = $(this).attr('href').split('page=')[1];
        const sort_by = $('#sort_by').val();
        const sort_order = $('#sort_order').val();
        const search = $('#search-input').val();
        const designation_id = $('#designation-filter').val();
        fetch_staff_data(page, sort_by, sort_order, search, designation_id);
    });

    setTimeout(function() { $('#success-alert').fadeOut('slow'); }, 5000);
});
</script>
@stop