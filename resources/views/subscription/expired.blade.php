@extends('layouts.payment-layout')

@section('title', 'Subscription Expired')

@section('content')
<div class="text-center mb-4">
    <a href="/">
        <x-application-logo class="w-20 h-20 fill-current text-gray-500 mx-auto" />
    </a>
</div>
<div class="card shadow border-danger">
    <div class="card-header bg-danger text-white text-center">
        <h4>Subscription Inactive</h4>
    </div>
    <div class="card-body p-4 text-center">
        <p class="lead">
            Your subscription has ended or has been deactivated by an administrator.
        </p>
        <p>
            To continue using our services, please renew your subscription or contact support for assistance.
        </p>
        <div class="mt-4">
            <a href="{{ route('landing') }}" class="btn btn-secondary">Return to Homepage</a>
            {{-- You can add a link to a 'contact us' page here if you have one --}}
        </div>
    </div>
</div>
@endsection