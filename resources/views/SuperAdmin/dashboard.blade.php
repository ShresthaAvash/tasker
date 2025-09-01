@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

{{-- THIS IS THE FIX: Added the correct CSS for the card footers --}}
@section('css')
<style>
    /* General Page & Card Styling */
    .content-wrapper {
        background-color: #f7f9fc;
    }
    .card {
        box-shadow: 0 4px 20px 0 rgba(0,0,0,0.05);
        border: none;
        border-radius: .75rem;
    }
    .card-header.bg-white {
        background-color: #fff !important;
        border-bottom: 1px solid #f0f0f0;
    }
    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #343a40;
    }

    /* Modern Stat Card Styling */
    .stat-card-modern {
        background-color: #fff;
        border-radius: .75rem;
        padding: 2.5rem;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    .stat-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .stat-card-content {
        display: flex;
        align-items: center;
        width: 100%;
    }
    .stat-card-modern .icon-wrapper {
        flex-shrink: 0;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 1.5rem;
    }
    .stat-card-modern .icon-wrapper i {
        font-size: 2rem;
    }
    .stat-card-modern .stat-info .stat-title {
        color: #6c757d;
        font-size: 1rem;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }
    .stat-card-modern .stat-info .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #212529;
        line-height: 1.2;
    }
    .stat-card-footer {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #f0f0f0;
        text-align: center;
    }
    .stat-card-footer a {
        color: #6c757d;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: color 0.2s ease-in-out;
    }
    .stat-card-footer a:hover {
        color: #0d6efd;
    }
    .stat-card-footer i {
        margin-left: 0.25rem;
        transition: transform 0.2s ease-in-out;
    }
    .stat-card-footer a:hover i {
        transform: translateX(3px);
    }

    /* Icon Colors */
    .bg-primary-light { background-color: #e3f2fd; }
    .text-primary { color: #0d6efd !important; }
    .bg-success-light { background-color: #d1e7dd; }
    .text-success { color: #198754 !important; }
    .bg-warning-light { background-color: #fff3cd; }
    .text-warning { color: #ffc107 !important; }
    .bg-danger-light { background-color: #f8d7da; }
    .text-danger { color: #dc3545 !important; }

    /* Recently Joined Organizations Card */
    .list-group-flush .list-group-item {
        border-color: #f0f0f0;
        transition: all 0.2s ease-in-out;
    }
    .list-group-flush .list-group-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    .card-footer {
        background-color: #fff !important;
        border-top: 1px solid #f0f0f0;
    }
    .card-footer a {
        font-weight: 600;
        color: #6c757d;
        text-decoration: none;
        transition: color 0.2s ease-in-out;
    }
    .card-footer a:hover {
        color: #0d6efd;
    }

    /* Quick Actions Card with new animations */
    .quick-actions-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .action-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 1rem;
        font-size: 1rem;
        font-weight: 600;
        border-radius: .75rem;
        transition: all 0.2s ease-in-out;
        border: 1px solid transparent;
    }
    .action-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 14px rgba(0,0,0,0.07);
    }
    .action-button i {
        font-size: 1.1rem;
        margin-right: 0.75rem;
    }
    .action-button.btn-primary-solid { 
        background-color: #0d6efd !important; 
        border-color: #0d6efd !important;
        color: #fff;
    }
    .action-button.btn-primary-solid:hover {
        background-color: #0b5ed7 !important;
        border-color: #0a58ca !important;
    }
    .action-button.btn-secondary-light { 
        background-color: #e3f2fd !important; 
        color: #0d6efd !important; 
        border-color: #e3f2fd !important;
    }
    .action-button.btn-secondary-light:hover {
        background-color: #d1e9fc !important;
        border-color: #d1e9fc !important;
    }
</style>
@stop

@section('content')
{{-- THIS IS THE FIX: The entire HTML structure for the info boxes has been replaced --}}
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-primary-light">
                    <i class="fas fa-building text-primary"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Total Organizations</p>
                    <h3 class="stat-number">{{ $organizationCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('superadmin.organizations.index') }}">
                    View All Organizations <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-success-light">
                    <i class="fas fa-tags text-success"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Subscription Plans</p>
                    <h3 class="stat-number">{{ $subscriptionPlansCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('superadmin.plans.index') }}">
                    View All Plans <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-warning-light">
                    <i class="fas fa-user-check text-warning"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Subscribed Orgs</p>
                    <h3 class="stat-number">{{ $subscribedOrgsCount }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('superadmin.subscriptions.subscribed') }}">
                    View Subscribers <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="stat-card-modern h-100">
            <div class="stat-card-content">
                <div class="icon-wrapper bg-danger-light">
                    <i class="fas fa-dollar-sign text-danger"></i>
                </div>
                <div class="stat-info">
                    <p class="stat-title">Total Earnings</p>
                    <h3 class="stat-number">${{ number_format($totalEarnings, 2) }}</h3>
                </div>
            </div>
            <div class="stat-card-footer">
                <a href="{{ route('superadmin.earnings') }}">
                    View Earnings Report <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Main Content Row - Redesigned --}}
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h3 class="card-title">Recently Joined Organizations</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($recentlyJoined as $org)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <strong>{{ $org->name }}</strong>
                            <span class="text-muted">{{ $org->created_at->diffForHumans() }}</span>
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
            <div class="card-header bg-white">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body quick-actions-grid">
                <a href="{{ route('superadmin.plans.create') }}" class="btn action-button btn-primary-solid">
                    <i class="fas fa-plus-circle"></i> Add Subscription
                </a>
                <a href="{{ route('superadmin.subscriptions.subscribed') }}" class="btn action-button btn-secondary-light">
                    <i class="fas fa-user-check"></i> View Subscribed
                </a>
            </div>
        </div>
    </div>
</div>
@stop