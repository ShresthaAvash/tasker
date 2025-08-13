@extends('layouts.app')
@section('page-content')
    <div class="card">
        <div class="card-header"><h4>Edit Subscription</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('superadmin.subscriptions.update', $subscription->id) }}">
                @csrf
                @method('PUT')
                @include('SuperAdmin.subscriptions._form', ['subscription' => $subscription])
                <button type="submit" class="btn btn-primary">Update Subscription</button>
                <a href="{{ route('superadmin.subscriptions.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection