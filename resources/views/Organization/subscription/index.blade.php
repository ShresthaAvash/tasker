@extends('layouts.app')

@section('title', 'My Subscription Plan')

@section('content_header')
    <h1>My Subscription Plan</h1>
@stop

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{ session('success') }}
    </div>
@endif

<div class="row">
    <!-- Current Plan Card -->
    <div class="col-md-5">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Current Plan</h3>
            </div>
            <div class="card-body">
                @if($currentSubscription)
                    <h4 class="text-info">{{ $currentSubscription->name }}</h4>
                    <p class="lead"><b>${{ number_format($currentSubscription->price, 2) }}</b> / {{ $currentSubscription->type }}</p>
                    <p class="text-muted">{{ $currentSubscription->description }}</p>
                @else
                    <p class="text-muted">You are not currently subscribed to any plan.</p>
                    <p>Please select a plan from the available options to continue.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Available Plans Card -->
    <div class="col-md-7">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Change Subscription</h3>
            </div>
            <form action="{{ route('organization.subscription.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <p>Select a new plan from the options below.</p>
                    @if($allSubscriptions->isNotEmpty())
                        @foreach($allSubscriptions as $subscription)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="subscription_id" id="sub_{{ $subscription->id }}" value="{{ $subscription->id }}" {{ optional($currentSubscription)->id == $subscription->id ? 'checked' : '' }}>
                            <label class="form-check-label" for="sub_{{ $subscription->id }}">
                                <strong>{{ $subscription->name }}</strong> - 
                                ${{ number_format($subscription->price, 2) }} / {{ $subscription->type }}
                            </label>
                        </div>
                        @endforeach
                    @else
                        <p class="text-danger">No subscription plans are available at the moment. Please contact support.</p>
                    @endif
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-info" {{ $allSubscriptions->isEmpty() ? 'disabled' : '' }}>Save Subscription</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop