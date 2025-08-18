@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Dashboard</h1>
        <a href="{{ route('generate.report') }}" class="btn btn-primary">
            <i class="fas fa-download mr-1"></i> Generate Report
        </a>
    </div>
@stop

@section('content')
{{-- Top Row Info Boxes --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $organizationCount }}</h3>
                <p>Total Organizations</p>
            </div>
            <div class="icon"><i class="fas fa-building"></i></div>
            <a href="{{ route('superadmin.organizations.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $subscriptionPlansCount }}</h3>
                <p>Subscription Plans</p>
            </div>
            <div class="icon"><i class="fas fa-tags"></i></div>
            <a href="{{ route('superadmin.subscriptions.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $subscribedOrgsCount }}</h3>
                <p>Subscribed Organizations</p>
            </div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
            <a href="{{ route('superadmin.organizations.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>${{ number_format($totalMonthlyEarnings, 2) }}</h3>
                <p>Estimated Monthly Earnings</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- Main Content Row --}}
<div class="row">
    <div class="col-md-8">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Recent Subscription Requests</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentRequests as $request)
                        <li class="list-group-item">
                            <strong>{{ $request->name }}</strong> ({{ $request->email }})
                            <span class="float-right text-muted">{{ $request->created_at->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">
                            No new subscription requests.
                        </li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('superadmin.subscriptions.requests') }}">View All Requests</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <a href="{{ route('superadmin.organizations.create') }}" class="btn btn-app bg-primary">
                    <i class="fas fa-building"></i> Add Organization
                </a>
                <a href="{{ route('superadmin.subscriptions.create') }}" class="btn btn-app bg-success">
                    <i class="fas fa-tags"></i> Add Subscription
                </a>
                <a href="{{ route('superadmin.subscriptions.requests') }}" class="btn btn-app bg-warning">
                    <i class="fas fa-hourglass-start"></i> View Requests
                </a>
            </div>
        </div>
    </div>
</div>
@stop