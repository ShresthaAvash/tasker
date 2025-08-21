@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
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
            {{-- MODIFIED TEXT --}}
            <a href="{{ route('superadmin.organizations.index') }}" class="small-box-footer">View Total Organizations <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $subscriptionPlansCount }}</h3>
                <p>Subscription Plans</p>
            </div>
            <div class="icon"><i class="fas fa-tags"></i></div>
            {{-- MODIFIED TEXT --}}
            <a href="{{ route('superadmin.plans.index') }}" class="small-box-footer">View All Subscription Plans <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $subscribedOrgsCount }}</h3>
                <p>Subscribed Organizations</p>
            </div>
            <div class="icon"><i class="fas fa-user-check"></i></div>
            {{-- MODIFIED TEXT --}}
            <a href="{{ route('superadmin.subscriptions.subscribed') }}" class="small-box-footer">View All Subscribed Organizations <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>${{ number_format($totalEarnings, 2) }}</h3>
                <p>Total Earnings</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            {{-- MODIFIED TEXT --}}
            <a href="{{ route('superadmin.earnings') }}" class="small-box-footer">View Total Earnings <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

{{-- Main Content Row --}}
<div class="row">
    <div class="col-md-8">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title">Recently Joined Organizations</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentlyJoined as $org)
                        <li class="list-group-item">
                            <strong>{{ $org->name }}</strong>
                            <span class="float-right text-muted">Joined {{ $org->created_at->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted text-center">
                            No new organizations have joined recently.
                        </li>
                    @endforelse
                </ul>
            </div>
            <div class="card-footer text-center">
                <a href="{{ route('superadmin.organizations.index') }}">View All Organizations</a>
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
                <a href="{{ route('superadmin.plans.create') }}" class="btn btn-app bg-success">
                    <i class="fas fa-tags"></i> Add Subscription
                </a>
                <a href="{{ route('superadmin.subscriptions.subscribed') }}" class="btn btn-app bg-warning">
                    <i class="fas fa-user-check"></i> View Subscribed
                </a>
            </div>
        </div>
    </div>
</div>
@stop