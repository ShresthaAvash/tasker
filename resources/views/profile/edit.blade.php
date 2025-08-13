@extends('layouts.app')

@section('title', 'My Profile')

@section('content_header')
    <h1>My Profile</h1>
@stop

@section('content')
    <div class="row">

        {{-- START: Left Column --}}
        <div class="col-md-6">

            {{-- Card 1: Profile Information --}}
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Profile Information</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Card 2: Delete Account (Stays on the left) --}}
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">Delete Account</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
        {{-- END: Left Column --}}

        {{-- START: Right Column --}}
        <div class="col-md-6">

            {{-- Card 3: Update Password --}}
            {{-- --- THIS IS THE FIX --- --}}
            {{-- The 'h-100' class has been removed to restore the natural height --}}
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h3 class="card-title">Update Password</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

        </div>
        {{-- END: Right Column --}}

    </div>
@stop