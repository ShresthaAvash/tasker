@extends('layouts.site')
 
@section('page-styles')
<style>
    /* Main Section Styling */
    .pricing-section {
        padding: 80px 0;
        background-color: #f8f9fa; /* Light grey background */
    }
    .pricing-header {
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 50px;
    }
    .pricing-header h2 {
        font-weight: 700;
        font-size: 2.8rem;
        color: #1a202c;
    }
    .pricing-header p {
        font-size: 1.15rem;
        color: #6c757d;
    }
   
    .pricing-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 30px; /* Space between cards */
    }
 
    /* Card Styling */
    .pricing-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 40px 30px; /* Vertical and horizontal padding */
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center; /* Center content horizontally */
        flex: 0 0 340px; /* Fixed width for cards */
        position: relative;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .pricing-card:not(.highlight):hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
 
    .pricing-card.highlight {
        background: linear-gradient(180deg, #4f46e5 0%, #3b82f6 100%);
        border: none;
        color: #fff;
        transform: scale(1.05); /* Makes the highlighted card slightly larger */
    }
    .pricing-card.highlight:hover {
         transform: scale(1.08); /* Slightly larger hover effect */
    }
    .pricing-card.highlight .plan-name,
    .pricing-card.highlight .price,
    .pricing-card.highlight .price-period,
    .pricing-card.highlight .plan-description {
        color: #fff;
    }
    .pricing-card.highlight .feature-list li { color: #e0ecff; }
    .pricing-card.highlight .feature-list .fa-check-circle { color: #fff; }
    .pricing-card.highlight .btn-purchase { background: #fff; color: #2563eb; }
 
    /* Most Popular Badge */
    .most-popular-badge {
        position: absolute;
        top: -14px; /* Position badge above the card */
        left: 50%;
        transform: translateX(-50%);
        background: #e3f2fd;
        color: #0a58ca;
        padding: 5px 15px;
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 14px; /* Pill shape */
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
   
    /* Typography */
    .plan-name { font-weight: 600; font-size: 1.1rem; margin-bottom: 10px; color: #1a202c; }
    .price { font-size: 3rem; font-weight: 700; color: #1a202c; }
    .price-period { font-size: 1rem; color: #6c757d; font-weight: 500; margin-bottom: 20px; }
    .plan-description { font-size: 0.95rem; color: #4a5568; margin-bottom: 25px; text-align: center; min-height: 40px; }
 
    /* Features */
    .feature-list { list-style: none; padding: 0; margin: 15px 0 30px 0; text-align: left; width: 100%; flex-grow: 1; }
    .feature-list li { margin-bottom: 14px; display: flex; align-items: center; font-size: 0.95rem; color: #4a5568;}
    .feature-list .fa-check-circle { color: #3b82f6; margin-right: 12px; font-size: 1.1rem; }
 
    /* Button */
    .btn-purchase {
        width: 100%;
        padding: 14px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        background: #3b82f6;
        color: #fff;
        border: 1px solid #3b82f6;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }
    .btn-purchase.btn-outline {
        background: transparent;
        color: #3b82f6;
    }
    .btn-purchase:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>
@endsection
 
@section('content')
<div class="pricing-section">
    <div class="container">
        <div class="pricing-header text-center">
            <h2>Choose Your Perfect Plan</h2>
            <p>Scale your productivity with Tasker. From personal task management to enterprise-grade solutions.</p>
        </div>
 
        @if(session('info'))
            <div class="alert alert-info mb-4 col-md-8 mx-auto">
                {{ session('info') }}
            </div>
        @endif
 
        <div class="pricing-container">
            @forelse($subscriptions as $plan)
                @php
                    // Logic to highlight a plan. This highlights the second plan by default.
                    // You could add a `is_popular` column to your database for more control.
                    $isHighlighted = $loop->iteration == 2;
                @endphp
                <div class="pricing-card {{ $isHighlighted ? 'highlight' : '' }}">
                    @if($isHighlighted)
                        <div class="most-popular-badge">Most Popular</div>
                    @endif
 
                    <h5 class="plan-name">{{ $plan->name }}</h5>
                    <h2 class="price">
                        £{{ number_format($plan->type == 'annually' && $plan->price > 0 ? $plan->price / 12 : $plan->price, 2) }}
                    </h2>
                    <p class="price-period">
                        Per {{ $plan->type == 'annually' ? 'year' : 'month' }} +VAT
                    </p>
 
                    <p class="plan-description">{{ $plan->description }}</p>
 
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Unlimited client & staff</li>
                        <li><i class="fas fa-check-circle"></i> Workflow & task automation</li>
                        <li><i class="fas fa-check-circle"></i> Advance task organization</li>
                        <li><i class="fas fa-check-circle"></i> Team tracking & reporting</li>
                        <li><i class="fas fa-check-circle"></i> Email & push notification</li>
                        <li><i class="fas fa-check-circle"></i> Mobile support</li>
                    </ul>
                   
                    @php
                        // Determine the correct button style
                        $buttonClass = $isHighlighted ? '' : 'btn-outline';
                    @endphp
 
                    @auth
                        @if(Auth::user()->subscribed('default') && Auth::user()->subscription('default')->stripe_price === $plan->stripe_price_id)
                            <button type="button" class="btn-purchase mt-auto" disabled>Current Plan</button>
                        @else
                             <a href="{{ route('subscription.checkout', ['plan' => $plan->id]) }}" class="btn-purchase mt-auto {{ $buttonClass }}">Get Started</a>
                        @endif
                    @else
                        <a href="{{ route('register', ['plan' => $plan->id]) }}" class="btn-purchase mt-auto {{ $buttonClass }}">Get Started</a>
                    @endauth
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        No subscription plans have been configured by the administrator yet. Please check back later.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
