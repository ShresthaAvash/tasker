@extends('layouts.app')

@section('title', 'Super Admin Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Super Admin Report</h1>
        <button onclick="window.print();" class="btn btn-primary d-print-none">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
@stop

@section('content')

{{-- Summary Info Boxes --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $organizationCount }}</h3>
                <p>Total Organizations</p>
            </div>
            <div class="icon"><i class="fas fa-building"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $subscriptionPlansCount }}</h3>
                <p>Subscription Plans</p>
            </div>
            <div class="icon"><i class="fas fa-tags"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $subscribedOrgsCount }}</h3>
                <p>Subscribed Orgs</p>
            </div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>${{ number_format($totalMonthlyEarnings, 2) }}</h3>
                <p>Est. Monthly Earnings</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
        </div>
    </div>
</div>

{{-- Detailed Lists --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-info card-outline">
            <div class="card-header"><h3 class="card-title">All Organizations</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($organizations as $org)
                        <li class="list-group-item">{{ $org->name }} <span class="float-right text-muted">{{ $org->email }}</span></li>
                    @empty
                        <li class="list-group-item text-muted">No organizations found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-success card-outline">
            <div class="card-header"><h3 class="card-title">All Subscription Plans</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($subscriptions as $sub)
                        <li class="list-group-item">{{ $sub->name }} <span class="badge badge-primary float-right">${{ number_format($sub->price, 2) }} / {{ $sub->type }}</span></li>
                    @empty
                        <li class="list-group-item text-muted">No subscription plans found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@stop