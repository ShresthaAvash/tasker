@extends('layouts.site')

@section('page-styles')
<style>
    .pricing-section {
        padding: 80px 0;
        background-color: #f8f9fa; /* Light grey background */
    }
    .pricing-header {
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 50px;
    }
    .pricing-header h2 {
        font-weight: 700;
        font-size: 2.5rem;
    }
    .pricing-header p {
        font-size: 1.1rem;
        color: #6c757d;
    }
    .pricing-card {
        background: #fff;
        border: 2px solid #dee2e6;
        border-radius: 12px;
        padding: 40px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
    }
    .pricing-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        border-color: #0d6efd;
    }
    .pricing-card-content {
        flex-grow: 1;
    }
    .pricing-card .plan-name {
        font-weight: 600;
        font-size: 1.2rem;
    }
    .price {
        font-size: 3.5rem;
        font-weight: 700;
    }
    .feature-list {
        list-style: none;
        padding: 0;
        margin: 20px 0;
        text-align: left;
    }
    .feature-list li {
        margin-bottom: 12px;
        display: flex;
        align-items: center;
    }
    .feature-list .fa-check-circle {
        color: #28a745;
        margin-right: 10px;
    }
    .btn-purchase {
        width: 100%;
        padding: 12px;
        font-size: 1.1rem;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.2s ease-in-out;
    }
    .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: #fff;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.25);
    }
</style>
@endsection

@section('content')
<div class="pricing-section">
    <div class="container">
        <div class="pricing-header text-center">
            <h2>Flexible pricing for teams of all sizes</h2>
            <p>Choose the plan that’s right for your practice. All plans come with a 14-day free trial.</p>
        </div>

        @php
            $monthlyPlan = $subscriptions->firstWhere('type', 'monthly');
            $annualPlan = $subscriptions->firstWhere('type', 'annually');
        @endphp

        @if(session('info'))
            <div class="alert alert-info mb-4 col-md-8 mx-auto">
                {{ session('info') }}
            </div>
        @endif

        @if($monthlyPlan && $annualPlan)
            <div class="row justify-content-center">
                {{-- Monthly Card --}}
                <div class="col-lg-5 mb-4">
                    <div class="pricing-card h-100">
                        <div class="pricing-card-content text-center">
                            <h5 class="plan-name">{{ $monthlyPlan->name }}</h5>
                            <h2 class="price my-3">£{{ number_format($monthlyPlan->price, 2) }}</h2>
                            <p class="text-muted">Per Month + VAT</p>
                            <hr>
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> Unlimited Clients & Staff</li>
                                <li><i class="fas fa-check-circle"></i> Workflow & Task Automation</li>
                                <li><i class="fas fa-check-circle"></i> Secure Client Portal</li>
                                <li><i class="fas fa-check-circle"></i> Time Tracking & Reporting</li>
                                <li><i class="fas fa-check-circle"></i> Email & Phone Support</li>
                            </ul>
                        </div>
                        
                        <div class="mt-auto">
                            @auth
                                @if(Auth::user()->subscribed('default') && Auth::user()->subscription('default')->stripe_price === $monthlyPlan->stripe_price_id)
                                    <button type="button" class="btn btn-secondary btn-purchase" disabled>Current Plan</button>
                                @else
                                    <a href="{{ route('subscription.checkout', ['plan' => $monthlyPlan->id]) }}" class="btn btn-outline-primary btn-purchase">Choose Plan</a>
                                @endif
                            @else
                                <a href="{{ route('register', ['plan' => $monthlyPlan->id]) }}" class="btn btn-outline-primary btn-purchase">Get Started</a>
                            @endauth
                        </div>
                    </div>
                </div>

                {{-- Annual Card --}}
                <div class="col-lg-5 mb-4">
                    <div class="pricing-card h-100">
                        <div class="pricing-card-content text-center">
                            <h5 class="plan-name">{{ $annualPlan->name }}</h5>
                            <h2 class="price my-3">£{{ number_format($annualPlan->price / 12, 2) }}</h2>
                            <p class="text-muted">Per Month + VAT</p>
                            <div class="badge bg-success my-2 fs-6">Save 20%</div>
                             <hr>
                            <ul class="feature-list">
                                <li><i class="fas fa-check-circle"></i> Unlimited Clients & Staff</li>
                                <li><i class="fas fa-check-circle"></i> Workflow & Task Automation</li>
                                <li><i class="fas fa-check-circle"></i> Secure Client Portal</li>
                                <li><i class="fas fa-check-circle"></i> Time Tracking & Reporting</li>
                                <li><i class="fas fa-check-circle"></i> Email & Phone Support</li>
                            </ul>
                        </div>
                        
                        <div class="mt-auto">
                            @auth
                                @if(Auth::user()->subscribed('default') && Auth::user()->subscription('default')->stripe_price === $annualPlan->stripe_price_id)
                                    <button type="button" class="btn btn-secondary btn-purchase" disabled>Current Plan</button>
                                @else
                                    {{-- THIS IS THE FIX: Changed btn-primary to btn-outline-primary --}}
                                    <a href="{{ route('subscription.checkout', ['plan' => $annualPlan->id]) }}" class="btn btn-outline-primary btn-purchase">Choose Plan</a>
                                @endif
                            @else
                                {{-- THIS IS THE FIX: Changed btn-primary to btn-outline-primary --}}
                                <a href="{{ route('register', ['plan' => $annualPlan->id]) }}" class="btn btn-outline-primary btn-purchase">Get Started</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning col-md-8 mx-auto">
                Pricing plans have not been configured correctly. Please ensure at least one 'monthly' and one 'annually' subscription exists.
            </div>
        @endif
    </div>
</div>
@endsection