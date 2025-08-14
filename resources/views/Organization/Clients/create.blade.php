@extends('layouts.app')

@section('title', 'Add New Client')

@section('content_header')
    <h1>Add New Client</h1>
@stop

@section('content')

{{-- This card component provides the new UI --}}
<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">Client Details</h3>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Client Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label for="email">Client Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="phone">Client Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
            </div>

            <div class="form-group">
                <label for="address">Client Address</label>
                <textarea class="form-control" id="address" name="address">{{ old('address') }}</textarea>
            </div>

            <div class="form-group">
                <label for="photo">Client Photo</label>
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="photo" name="photo" accept="image/*">
                    <label class="custom-file-label" for="photo">Choose file</label>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="A" {{ old('status') == 'A' ? 'selected' : '' }}>Active</option>
                    <option value="I" {{ old('status') == 'I' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <hr>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
            </div>

            {{-- --- THIS IS THE FIX --- --}}
            {{-- We change the button class from btn-primary to btn-info --}}
            <button type="submit" class="btn btn-info">Add Client</button>
            <a href="{{ route('clients.index') }}" class="btn btn-default">Cancel</a>
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