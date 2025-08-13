@extends('layouts.app')

@section('title', 'Add Staff')

@section('content_header')
    <h1>Add New Staff Member</h1>
@stop

@section('content')
<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">Staff Details</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>

            <div class="form-group">
                <label for="staff_designation_id">Designation</label>
                <select name="staff_designation_id" id="staff_designation_id" class="form-control @error('staff_designation_id') is-invalid @enderror">
                    <option value="">-- Select Designation --</option>
                    @foreach($designations as $designation)
                        <option value="{{ $designation->id }}" {{ old('staff_designation_id') == $designation->id ? 'selected' : '' }}>{{ $designation->name }}</option>
                    @endforeach
                </select>
                @error('staff_designation_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            
            <div class="row">
                <div class="form-group col-md-6">
                    <label for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                    @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="A" {{ old('status', 'A') == 'A' ? 'selected' : '' }}>Active</option>
                        <option value="I" {{ old('status') == 'I' ? 'selected' : '' }}>Inactive</option>
                    </select>
                     @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                @error('address') <span class="invalid-feedback">{{ $message }}</span> @enderror
            </div>
            
            <div class="form-group">
                <label for="photo">Photo</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*">
                    <label class="custom-file-label" for="photo">Choose file</label>
                </div>
                @error('photo') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
            </div>

            <hr>
            
            <div class="row">
                 <div class="form-group col-md-6">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-info">Add Staff Member</button>
            <a href="{{ route('staff.index') }}" class="btn btn-default">Cancel</a>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    // This script makes the file input show the selected file name
    $(function () {
        bsCustomFileInput.init();
    });
</script>
@stop