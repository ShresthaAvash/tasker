@extends('layouts.app')

@section('title', 'Add Plan')

@section('content_header')
    <h1>Add New Subscription Plan</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header"><h4>Plan Details</h4></div>
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.plans.store') }}">
            @csrf
            @include('SuperAdmin.plans._form')
            <button type="submit" class="btn btn-primary mt-3">Create Plan</button>
             <a href="{{ route('superadmin.plans.index') }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
</div>
@endsection