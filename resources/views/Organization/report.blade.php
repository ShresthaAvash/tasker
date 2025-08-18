@extends('layouts.app')

@section('title', 'Account Report')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Account Report</h1>
        <button onclick="window.print();" class="btn btn-primary d-print-none">
            <i class="fas fa-print"></i> Print Report
        </button>
    </div>
@stop

@section('content')

{{-- Summary Info Boxes --}}
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info"><div class="inner"><h3>{{ $clientCount }}</h3><p>Active Clients</p></div><div class="icon"><i class="fas fa-users"></i></div></div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success"><div class="inner"><h3>{{ $staffCount }}</h3><p>Staff Members</p></div><div class="icon"><i class="fas fa-user-tie"></i></div></div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger"><div class="inner"><h3>{{ $serviceCount }}</h3><p>Total Services</p></div><div class="icon"><i class="fas fa-concierge-bell"></i></div></div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $taskStatusCounts->sum() }}</h3>
                <p>Total Tasks in Templates</p>
            </div>
            <div class="icon"><i class="fas fa-tasks"></i></div>
        </div>
    </div>
</div>

{{-- Detailed Lists --}}
<div class="row">
    <div class="col-md-4">
        <div class="card card-info card-outline">
            <div class="card-header"><h3 class="card-title">Active Clients</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($clients as $client)
                        <li class="list-group-item">{{ $client->name }} <span class="float-right text-muted">{{ $client->email }}</span></li>
                    @empty
                        <li class="list-group-item text-muted">No clients found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-success card-outline">
            <div class="card-header"><h3 class="card-title">Staff Members</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($staff as $staffMember)
                        <li class="list-group-item">{{ $staffMember->name }} <span class="float-right text-muted">{{ $staffMember->email }}</span></li>
                    @empty
                        <li class="list-group-item text-muted">No staff found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-danger card-outline">
            <div class="card-header"><h3 class="card-title">Services</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($services as $service)
                        <li class="list-group-item">{{ $service->name }} <span class="badge badge-primary float-right">{{ $service->jobs_count }} Jobs</span></li>
                    @empty
                        <li class="list-group-item text-muted">No services found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@stop