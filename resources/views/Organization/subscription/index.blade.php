@extends('layouts.app')
 
@section('title', 'My Subscription')
 
@section('content_header')
    <h1>My Subscription</h1>
@stop
 
@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        {{ session('error') }}
    </div>
@endif
 
<div class="card card-info card-outline card-tabs">
    <div class="card-header p-0 pt-1 border-bottom-0">
        <ul class="nav nav-tabs" id="subscription-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="current-plan-tab" data-toggle="pill" href="#current-plan" role="tab">Current Plan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="history-tab" data-toggle="pill" href="#history" role="tab">Billing History</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="subscription-tabs-content">
            {{-- Current Plan Tab --}}
            <div class="tab-pane fade show active" id="current-plan" role="tabpanel">
                @if ($currentSubscription && $plan)
                    <h3 class="text-info">{{ $plan->name }}</h3>
                    <p class="lead"><b>${{ number_format($plan->price, 2) }}</b> / {{ $plan->type }}</p>
                    <p class="text-muted">{{ $plan->description }}</p>
                    <hr>
                    <p><strong>Started On:</strong> {{ $currentSubscription->created_at->format('d M Y') }}</p>
                    <p>
                        <strong>Status:</strong>
                        @if ($currentSubscription->canceled())
                            <span class="badge badge-warning">Canceled</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </p>
                    <p>
                        <strong>
                            @if ($currentSubscription->canceled())
                                Ends on:
                            @else
                                Renews on:
                            @endif
                        </strong>
                        @if ($currentSubscription->calculated_ends_at)
                            {{ $currentSubscription->calculated_ends_at->format('d M Y') }}
                        @else
                            N/A
                        @endif
                    </p>
                    <a href="{{ route('pricing') }}" class="btn btn-primary mt-3">Change Plan</a>
                @else
                    <p class="text-muted">You are not currently subscribed to any plan.</p>
                    <a href="{{ route('pricing') }}" class="btn btn-primary">View Plans</a>
                @endif
            </div>
 
            {{-- Billing History Tab --}}
            <div class="tab-pane fade" id="history" role="tabpanel">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th class="text-right">Amount</th>
                            <th>Ends On</th>
                            <th class="text-center">Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            @foreach($invoice->lines->data as $line)
                                @if($line->type === 'subscription')
                                    <tr>
                                        <td>{{ $invoice->date()->toFormattedDateString() }}</td>
                                        <td>{!! $line->description !!}</td>
                                        <td class="text-right">{{ $invoice->total() }}</td>
 
                                        {{-- Ends On Date: always show local DB subscription ends_at --}}
                                        @php
                                            $subscription = Auth::user()->subscription('default');
                                        @endphp
                                        <td>
                                            @if($subscription && $subscription->ends_at)
                                                {{ $subscription->ends_at->toFormattedDateString() }}
                                            @else
                                                {{ \Carbon\Carbon::createFromTimestamp($line->period->end)->toFormattedDateString() }}
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ $invoice->invoice_pdf }}"
                                            class="btn btn-xs btn-secondary"
                                            target="_blank"
                                            title="Download Invoice">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No billing history found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
 
        </div>
    </div>
</div>
@stop