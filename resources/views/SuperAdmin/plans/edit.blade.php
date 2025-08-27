@extends('layouts.app')

@section('title', 'Edit Plan')

@section('content_header')
    <h1>Edit Subscription Plan</h1>
@stop

@section('content')
{{-- MODIFIED: Changed card theme to primary --}}
<div class="card card-primary card-outline">
    <div class="card-header"><h4>Edit Plan</h4></div>
    <div class="card-body">
        <form method="POST" action="{{ route('superadmin.plans.update', $plan->id) }}">
            @csrf
            @method('PUT')
            @include('SuperAdmin.plans._form', ['plan' => $plan])
            <button type="submit" class="btn btn-primary mt-3">Update Plan</button>
            <a href="{{ route('superadmin.plans.index') }}" class="btn btn-secondary mt-3">Cancel</a>
        </form>
    </div>
</div>
@endsection