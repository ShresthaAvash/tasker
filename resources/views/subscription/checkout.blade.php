@extends('layouts.payment-layout')

@section('title', 'Subscription Checkout')

@section('page-styles')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .checkout-wrapper {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        animation: fadeInUp 0.7s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    }

    .order-summary {
        background-color: #f8f9fa;
        padding: 2.5rem;
        border-right: 1px solid #e5e7eb;
    }

    .app-logo {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #111827;
        text-decoration: none;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .order-summary .price {
        font-size: 3rem;
        font-weight: 700;
        color: #111827;
        flex-shrink: 0; /* Prevents the price from shrinking */
    }

    .payment-form {
        padding: 2.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
    }
    
    .form-control, .StripeElement {
        background-color: #f9fafb;
        border-radius: 8px;
        padding: 0.85rem 1rem;
        border: 1px solid #d1d5db;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        font-size: 1rem;
    }

    .form-control:focus, .StripeElement--focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        background-color: #fff;
    }
    
    #card-button {
        border-radius: 8px;
        padding: 0.9rem;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.2s ease-in-out;
        background: #2563eb;
        border: none;
    }

    #card-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(59, 130, 246, 0.2), 0 3px 6px rgba(0, 0, 0, 0.08);
    }

    #card-button:active {
        transform: translateY(0);
        box-shadow: none;
    }

    #card-button:disabled {
        opacity: 0.65;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
</style>
@endsection

@section('content')
<div class="checkout-wrapper">
    <div class="row g-0">
        <!-- Left Column: Order Summary -->
        <div class="col-lg-5 order-summary">
            <div class="d-flex flex-column h-100">
                <div class="mb-5">
                    <a href="/" class="app-logo">
                        <i class="fa-solid fa-shield-halved fa-2x text-primary"></i>
                        <span>Tasker</span>
                    </a>
                </div>
                <h4 class="card-title mb-4">Order Summary</h4>
                
                {{-- --- THIS IS THE MODIFIED SECTION --- --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    {{-- Added me-3 class to create a margin on the right --}}
                    <h5 class="mb-0 me-3">{{ $plan->name }}</h5> 
                    <span class="price">£{{ number_format($plan->price, 2) }}</span>
                </div>
                {{-- --- END OF MODIFICATION --- --}}

                <p class="text-muted">{{ $plan->description }}</p>
                
                <hr class="my-4">
                
                <ul class="list-unstyled mb-4">
                    <li class="mb-2 d-flex align-items-center"><i class="fas fa-check-circle text-success me-2"></i>Full Feature Access</li>
                    <li class="mb-2 d-flex align-items-center"><i class="fas fa-check-circle text-success me-2"></i>Billed {{ $plan->type }}</li>
                    <li class="mb-2 d-flex align-items-center"><i class="fas fa-check-circle text-success me-2"></i>Cancel Anytime</li>
                </ul>

                <div class="mt-auto alert alert-secondary text-center border-0" role="alert">
                    <i class="fas fa-lock me-2"></i> Secure SSL Encrypted Payment
                </div>
            </div>
        </div>

        <!-- Right Column: Payment Form -->
        <div class="col-lg-7 payment-form">
            <h4 class="card-title mb-1">Subscribe to {{ $plan->name }}</h4>
            <p class="text-muted mb-4">Complete your secure payment below.</p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first('message') }}
                </div>
            @endif

            <form id="payment-form" action="{{ $formActionRoute }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">

                <div class="form-group mb-3">
                    <label for="organization-name" class="form-label">Organization Name</label>
                    <input id="organization-name" type="text" class="form-control bg-light" value="{{ $user->name }}" disabled readonly>
                </div>

                <div class="form-group mb-3">
                    <label for="card-holder-name" class="form-label">Card Holder Name</label>
                    <input id="card-holder-name" type="text" class="form-control" placeholder="Enter the name on the card" required>
                </div>

                <div class="form-group mb-4">
                    <label for="card-element" class="form-label">Credit or debit card</label>
                    <div id="card-element"></div>
                    <div id="card-errors" role="alert" class="text-danger mt-2"></div>
                </div>

                <div class="d-grid">
                    <button id="card-button" class="btn btn-primary btn-lg" data-secret="{{ $intent->client_secret }}">
                        <span class="button-text">
                            <i class="fas fa-lock"></i> Subscribe Now (£{{ number_format($plan->price, 2) }}/{{ $plan->type == 'monthly' ? 'mo' : 'yr' }})
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ config('services.stripe.key') }}');
    
    const elements = stripe.elements({
        fonts: [{
            cssSrc: 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
        }],
    });
    
    const cardStyle = {
        base: {
            color: '#32325d',
            fontFamily: 'Inter, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    };
    
    const cardWrapper = document.getElementById('card-element');
    const cardElement = elements.create('card', { style: cardStyle });
    cardElement.mount(cardWrapper);

    const cardHolderNameInput = document.getElementById('card-holder-name');
    const cardButton = document.getElementById('card-button');
    const clientSecret = cardButton.dataset.secret;
    const paymentForm = document.getElementById('payment-form');
    const cardErrors = document.getElementById('card-errors');

    paymentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        cardButton.disabled = true;
        cardButton.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...`;

        const { setupIntent, error } = await stripe.confirmCardSetup(
            clientSecret, {
                payment_method: {
                    card: cardElement,
                    billing_details: { name: cardHolderNameInput.value }
                }
            }
        );

        if (error) {
            cardErrors.textContent = error.message;
            cardButton.disabled = false;
            cardButton.innerHTML = `<span class="button-text"><i class="fas fa-lock"></i> Subscribe Now (£{{ number_format($plan->price, 2) }}/{{ $plan->type == 'monthly' ? 'mo' : 'yr' }})</span>`;
        } else {
            cardErrors.textContent = '';
            let tokenInput = document.createElement('input');
            tokenInput.setAttribute('type', 'hidden');
            tokenInput.setAttribute('name', 'payment_method');
            tokenInput.setAttribute('value', setupIntent.payment_method);
            paymentForm.appendChild(tokenInput);
            paymentForm.submit();
        }
    });
</script>
@endsection