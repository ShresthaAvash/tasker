@extends('layouts.app')

@section('title', 'Client Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-3">
        {{-- This is now the primary blue color --}}
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Tasks</p>
            </div>
            <div class="icon"><i class="fas fa-tasks"></i></div>
            <a href="{{ route('client.reports.index', ['month' => 'all']) }}" class="small-box-footer">View All Tasks <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['to_do'] }}</h3>
                <p>Tasks To Do</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-start"></i></div>
            <a href="{{ route('client.reports.index', ['month' => 'all', 'statuses' => ['to_do']]) }}" class="small-box-footer">View Tasks To Do <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3">
        {{-- Using the info color for variety within the blue theme --}}
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['ongoing'] }}</h3>
                <p>Ongoing Tasks</p>
            </div>
            <div class="icon"><i class="fas fa-spinner"></i></div>
            <a href="{{ route('client.reports.index', ['month' => 'all', 'statuses' => ['ongoing']]) }}" class="small-box-footer">View Ongoing Tasks <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['completed'] }}</h3>
                <p>Completed Tasks</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('client.reports.index', ['month' => 'all', 'statuses' => ['completed']]) }}" class="small-box-footer">View Completed Tasks <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Recent Documents</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentDocuments as $document)
                <tr>
                    <td>{{ $document->name }}</td>
                    <td><span class="badge {{ $document->uploader->type === 'C' ? 'badge-primary' : 'badge-info' }}">{{ $document->uploader->name }}</span></td>
                    <td>{{ $document->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('client.documents.download', $document) }}" class="btn btn-xs btn-secondary">
                            <i class="fas fa-download"></i> Download
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No documents found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer text-center">
        <a href="{{ route('client.documents.index') }}">View All Documents</a>
    </div>
</div>
@stop