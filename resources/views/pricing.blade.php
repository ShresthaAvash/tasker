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
        display: flex; /* <-- ADD: Use flexbox for layout */
        flex-direction: column; /* <-- ADD: Stack items vertically */
    }
    .pricing-card .card-body-content {
        flex-grow: 1; /* <-- ADD: Makes this section fill available space */
    }
    .pricing-card.highlight {
        border-color: #0d6efd;
        border-width: 2px;
        transform: scale(1.05);
    }
    .price { font-size: 3rem; font-weight: 700; }
    .slider-container { max-width: 500px; }
</style>
@endsection

@section('content')
<div class="pricing-section">
    <div class="container text-center">
        @php
            $monthlyPlan = $subscriptions->firstWhere('type', 'monthly');
            $annualPlan = $subscriptions->firstWhere('type', 'annually');
        @endphp

        @if($monthlyPlan && $annualPlan)
            <div class="row justify-content-center">
                {{-- Monthly Card --}}
                <div class="col-lg-4 mb-4">
                    <div class="pricing-card h-100">
                        <div class="card-body-content">
                            <h5>{{ $monthlyPlan->name }}</h5>
                            <h2 class="price my-3">£<span id="monthly-price-per-user">0.00</span></h2>
                            <p class="text-muted">Per User Per Month + VAT</p>
                            <hr>
                            <p><strong class="fs-5">£<span id="monthly-total">0.00</span></strong> / Paid monthly</p>
                            <p class="text-muted small">(<span class="monthly-price-note"></span> / user / month)</p>
                        </div>
                        {{-- --- THIS IS THE MODIFIED BUTTON LOGIC --- --}}
                        @auth
                            <form action="{{ route('organization.subscription.swap') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $monthlyPlan->id }}">
                                @if(Auth::user()->subscription('default') && Auth::user()->subscription('default')->stripe_price === $monthlyPlan->stripe_price_id)
                                    <button type="button" class="btn btn-secondary btn-lg mt-3" disabled>Current Plan</button>
                                @else
                                    <button type="submit" class="btn btn-outline-primary btn-lg mt-3">Switch Plan</button>
                                @endif
                            </form>
                        @else
                            <a href="{{ route('register', ['plan' => $monthlyPlan->id]) }}" class="btn btn-outline-primary btn-lg mt-3">Purchase</a>
                        @endauth
                    </div>
                </div>

                {{-- Annual Card --}}
                <div class="col-lg-4 mb-4">
                    <div class="pricing-card highlight h-100">
                        <div class="card-body-content">
                            <h5>{{ $annualPlan->name }}</h5>
                            <h2 class="price my-3">£<span id="annual-price-per-user">0.00</span></h2>
                            <p class="text-muted">Per User Per Month + VAT</p>
                            <div class="badge bg-success my-2 fs-6">Save 20%</div>
                            <hr>
                            <p><strong class="fs-5">£<span id="annual-total">0.00</span></strong> / year</p>
                            <p class="text-muted small">paid annually in advance</p>
                        </div>
                        {{-- --- THIS IS THE MODIFIED BUTTON LOGIC --- --}}
                        @auth
                            <form action="{{ route('organization.subscription.swap') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $annualPlan->id }}">
                                @if(Auth::user()->subscription('default') && Auth::user()->subscription('default')->stripe_price === $annualPlan->stripe_price_id)
                                    <button type="button" class="btn btn-secondary btn-lg mt-3" disabled>Current Plan</button>
                                @else
                                    <button type="submit" class="btn btn-primary btn-lg mt-3">Switch Plan</button>
                                @endif
                            </form>
                        @else
                            <a href="{{ route('register', ['plan' => $annualPlan->id]) }}" class="btn btn-primary btn-lg mt-3">Purchase</a>
                        @endauth
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

@section('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($monthlyPlan && $annualPlan)
            const baseMonthlyPrice = parseFloat("{{ $monthlyPlan->price }}");
            const baseAnnualPrice = parseFloat("{{ $annualPlan->price }}");
            const slider = document.getElementById('user-slider');
            const userInput = document.getElementById('user-input');
            const userCountDisplay = document.getElementById('user-count-display');
            const monthlyPricePerUserEl = document.getElementById('monthly-price-per-user');
            const annualPricePerUserEl = document.getElementById('annual-price-per-user');
            const monthlyTotalEl = document.getElementById('monthly-total');
            const annualTotalEl = document.getElementById('annual-total');
            const monthlyPriceNoteEl = document.querySelector('.monthly-price-note');
            function getDiscountRate(userCount) {
                if (userCount <= 10) return 0;
                if (userCount <= 25) return 0.05;
                if (userCount <= 50) return 0.10;
                return 0.15;
            }
            function updatePrices() {
                const userCount = parseInt(userInput.value);
                userCountDisplay.textContent = userCount;
                if (document.activeElement !== slider) slider.value = userCount;
                if (document.activeElement !== userInput) userInput.value = userCount;
                const discountRate = getDiscountRate(userCount);
                const discountedMonthlyPrice = baseMonthlyPrice * (1 - discountRate);
                const discountedAnnualPrice = baseAnnualPrice * (1 - discountRate);
                const totalMonthly = userCount * discountedMonthlyPrice;
                const totalAnnual = userCount * discountedAnnualPrice * 12;
                monthlyPricePerUserEl.textContent = discountedMonthlyPrice.toFixed(2);
                annualPricePerUserEl.textContent = discountedAnnualPrice.toFixed(2);
                monthlyTotalEl.textContent = totalMonthly.toFixed(2);
                annualTotalEl.textContent = totalAnnual.toFixed(2);
                monthlyPriceNoteEl.textContent = discountedMonthlyPrice.toFixed(2);
            }
            slider.addEventListener('input', () => {
                userInput.value = slider.value;
                updatePrices();
            });
            userInput.addEventListener('input', () => {
                if (parseInt(userInput.value) > 100) userInput.value = 100;
                if (parseInt(userInput.value) < 1) userInput.value = 1;
                slider.value = userInput.value;
                updatePrices();
            });
            updatePrices();
        @endif
    });
</script>
@endsection