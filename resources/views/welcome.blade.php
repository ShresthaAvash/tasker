@extends('layouts.site')

@section('page-styles')
<style>
    .hero-section {
        position: relative;
        padding: 120px 0;
        color: #333;
        background-color: #fff; /* Use a white background */
        min-height: 80vh; /* Ensure it takes up significant space */
        display: flex;
        align-items: center;
        overflow: hidden;
    }
    .hero-content {
        position: relative;
        z-index: 3;
    }
    .hero-shape {
        position: absolute;
        top: 50%; /* Center vertically */
        left: -150px;
        transform: translateY(-50%); /* Adjust vertical centering */
        width: 700px;
        height: 700px;
        background-color: #f08a5d; /* Your orange color */
        border-radius: 50%;
        z-index: 2;
    }
    .hero-section h1 {
        font-size: 3.5rem;
        font-weight: 600;
    }
    .hero-section p {
        font-size: 1.25rem;
        max-width: 600px;
    }
</style>
@endsection

@section('content')
<div class="hero-section">
    <div class="hero-shape"></div>
    <div class="container">
        <div class="row">
            <div class="col-lg-7 hero-content">
                <h1>Tasker powers up your practice</h1>
                <p class="my-4">
                    With an easy-to-configure CRM, powerful workflow, integrations, and a user-friendly client
                    portal, Tasker helps you power up your accountancy or bookkeeping practice. We'll help you
                    grow your firm and give great service to your clients, every time.
                </p>
                <a href="#" class="btn btn-primary btn-lg me-2">Play Demo</a>
                <a href="{{ route('pricing') }}" class="btn btn-secondary btn-lg">View Pricing Plan</a>
            </div>
        </div>
    </div>
</div>
@endsection