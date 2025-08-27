@extends('layouts.app')

@section('title', 'Add Organization')

@section('content_header')
    <h1>Add New Organization</h1>
@stop

@section('content')
<div class="card card-primary card-outline">
    <div class="card-header">
        <h3 class="card-title">Organization Details</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.organizations.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Create Organization</button>
            <a href="{{ route('superadmin.organizations.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@stop