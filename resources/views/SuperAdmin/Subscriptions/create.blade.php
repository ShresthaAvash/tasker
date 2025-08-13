@extends('layouts.app')
@section('page-content')
    <div class="card">
        <div class="card-header"><h4>Add New Subscription</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ route('superadmin.subscriptions.store') }}">
                @csrf
                @include('SuperAdmin.subscriptions._form')
                <button type="submit" class="btn btn-primary">Create Subscription</button>
                 <a href="{{ route('superadmin.subscriptions.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection