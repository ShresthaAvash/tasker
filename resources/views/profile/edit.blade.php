@extends('layouts.app')

@section('title', 'My Profile')

@section('content_header')
    <h1>My Profile</h1>
@stop

@section('css')
<style>
    /* Custom styles for a more modern profile page */
    .profile-card {
        transition: all 0.3s;
    }
    .profile-card .card-header {
        border-bottom: 1px solid #e3e6f0;
    }
    .profile-user-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 0 10px rgba(0,0,0,.1);
    }
    .profile-username {
        font-size: 1.5rem;
        font-weight: 300;
    }
    .text-muted {
        font-size: 0.875rem;
    }
</style>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-4">
            {{-- User Profile Card --}}
            <div class="card card-primary card-outline profile-card">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ Auth::user()->photo ? asset('storage/' . Auth::user()->photo) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim(Auth::user()->email))) . '?d=mp' }}"
                             alt="User profile picture">
                    </div>
                    <h3 class="profile-username text-center">{{ Auth::user()->name }}</h3>
                    <p class="text-muted text-center">{{ Auth::user()->email }}</p>
                </div>
            </div>

            {{-- Delete Account Card --}}
            <div class="card card-danger card-outline profile-card">
                <div class="card-header">
                    <h3 class="card-title">Delete Account</h3>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            {{-- Update Profile and Password Card with Tabs --}}
            <div class="card card-primary card-outline card-tabs profile-card">
                <div class="card-header p-0 pt-1 border-bottom-0">
                    <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="profile-info-tab" data-toggle="pill" href="#profile-info-content" role="tab">Profile Information</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="update-password-tab" data-toggle="pill" href="#update-password-content" role="tab">Update Password</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="custom-tabs-three-tabContent">
                        <div class="tab-pane fade show active" id="profile-info-content" role="tabpanel">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                        <div class="tab-pane fade" id="update-password-content" role="tabpanel">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

{{-- NEW: JavaScript to initialize the custom file input --}}
@section('js')
<script>
    $(function () {
        bsCustomFileInput.init();
    });
</script>
@stop