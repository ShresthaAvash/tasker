@extends('layouts.app')

@section('page-content')
    <h3>All Organizations</h3>

    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?php echo e(request('search')); ?>">
            <button class="btn btn-primary">Search</button>
        </div>
    </form>

    <a href="<?php echo e(route('superadmin.organizations.create')); ?>" class="btn btn-success mb-3">+ Add Organization</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Subscription</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($organizations as $org)
                <tr>
                    <td><?php echo e($org->name); ?></td>
                    <td><?php echo e($org->email); ?></td>
                    <td>
                        @if($org->subscribed('default'))
                            <span class="badge bg-info">Subscribed</span>
                        @else
                            <span class="badge bg-secondary">Not Subscribed</span>
                        @endif
                    </td>
                    <td>
                        @if ($org->status === 'A')
                            <span class="badge bg-success">Active</span>
                        @elseif ($org->status === 'R')
                            <span class="badge bg-warning">Requested</span>
                        @else
                            <span class="badge bg-danger">Suspended</span>
                        @endif
                    </td>
                    <td>
                        <a href="<?php echo e(route('superadmin.organizations.show', $org->id)); ?>" class="btn btn-info btn-sm">View</a>
                        <a href="<?php echo e(route('superadmin.organizations.edit', $org->id)); ?>" class="btn btn-warning btn-sm">Edit</a>
                        <form method="POST" action="<?php echo e(route('superadmin.organizations.destroy', $org->id)); ?>" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Toggle status?')">
                                <?php echo e($org->status === 'A' ? 'Suspend' : 'Activate'); ?>

                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No organizations found.</td></tr>
            @endforelse
        </tbody>
    </table>
@endsection