@extends('layouts.site')

@section('page-styles')
<style>
    .pricing-section { padding: 80px 0; background-color: #f8f9fa; }
    .pricing-card {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 30px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .pricing-card .card-body-content {
        flex-grow: 1;
    }
    .pricing-card.highlight {
        border-color: #0d6efd;
        border-width: 2px;
        transform: scale(1.05);
    }
    .price { font-size: 3rem; font-weight: 700; }
</style>
@endsection

@section('content')
<div class="pricing-section">
    <div class="container text-center">
        @php
            $monthlyPlan = $subscriptions->firstWhere('type', 'monthly');
            $annualPlan = $subscriptions->firstWhere('type', 'annually');
        @endphp

        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($monthlyPlan && $annualPlan)
            <div class="row justify-content-center">
                {{-- Monthly Card --}}
                <div class="col-lg-4 mb-4">
                    <div class="pricing-card h-100">
                        <div class="card-body-content">
                            <h5>{{ $monthlyPlan->name }}</h5>
                            <h2 class="price my-3">£{{ number_format($monthlyPlan->price, 2) }}</h2>
                            <p class="text-muted">Per Month + VAT</p>
                            <hr>
                            <p><strong class="fs-5">£{{ number_format($monthlyPlan->price, 2) }}</strong> / Paid monthly</p>
                            <p class="text-muted small">(Billed monthly)</p>
                        </div>
                        
                        @auth
                            {{-- --- THIS IS THE DEFINITIVE FIX: Use GET method to go to the checkout page --- --}}
                            <form action="{{ route('organization.subscription.change') }}" method="GET">
                                <input type="hidden" name="plan" value="{{ $monthlyPlan->id }}">
                                @if(Auth::user()->subscription('default') && Auth::user()->subscription('default')->stripe_price === $monthlyPlan->stripe_price_id)
                                    <button type="button" class="btn btn-secondary btn-lg mt-3" disabled>Current Plan</button>
                                @else
                                    <button type="submit" class="btn btn-outline-primary btn-lg mt-3">Switch Plan</button>
                                @endif
                            </form>
                        @else
                            <a href="{{ route('register', ['plan' => $monthlyPlan->id]) }}" class="btn btn-outline-primary btn-lg mt-3">Purchase</a>
                        @endif
                    </div>
                </div>

                {{-- Annual Card --}}
                <div class="col-lg-4 mb-4">
                    <div class="pricing-card highlight h-100">
                        <div class="card-body-content">
                            <h5>{{ $annualPlan->name }}</h5>
                            <h2 class="price my-3">£{{ number_format($annualPlan->price / 12, 2) }}</h2>
                            <p class="text-muted">Per Month + VAT</p>
                            <div class="badge bg-success my-2 fs-6">Save 20%</div>
                            <hr>
                            <p><strong class="fs-5">£{{ number_format($annualPlan->price, 2) }}</strong> / year</p>
                            <p class="text-muted small">paid annually in advance</p>
                        </div>
                        
                        @auth
                            <form action="{{ route('organization.subscription.change') }}" method="GET">
                                <input type="hidden" name="plan" value="{{ $annualPlan->id }}">
                                @if(Auth::user()->subscription('default') && Auth::user()->subscription('default')->stripe_price === $annualPlan->stripe_price_id)
                                    <button type="button" class="btn btn-secondary btn-lg mt-3" disabled>Current Plan</button>
                                @else
                                    <button type="submit" class="btn btn-primary btn-lg mt-3">Switch Plan</button>
                                @endif
                            </form>
                        @else
                            <a href="{{ route('register', ['plan' => $annualPlan->id]) }}" class="btn btn-primary btn-lg mt-3">Purchase</a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                Pricing plans have not been configured correctly. Please ensure at least one 'monthly' and one 'annually' subscription exists.
            </div>
        @endif
    </div>
</div>
@endsection