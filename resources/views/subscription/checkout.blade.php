@extends('layouts.payment-layout')

@section('title', 'Subscription Checkout')

@section('content')
<div class="text-center mb-4">
    <a href="/">
        <x-application-logo class="w-20 h-20 fill-current text-gray-500 mx-auto" />
    </a>
</div>
<div class="card shadow">
    <div class="card-header bg-primary text-white text-center">
        <h4>Subscribe to {{ $plan->name }}</h4>
        <p class="mb-0">Complete your secure payment below.</p>
    </div>
    <div class="card-body p-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first('message') }}
            </div>
        @endif

        <form id="payment-form" action="{{ route('subscription.store') }}" method="POST">
            @csrf
            <input type="hidden" name="plan_id" value="{{ $plan->id }}">

            <div class="form-group mb-3">
                <label for="organization-name" class="form-label">Organization Name</label>
                <input id="organization-name" type="text" class="form-control" value="{{ $user->name }}" disabled readonly>
            </div>

            <div class="form-group mb-3">
                <label for="card-holder-name" class="form-label">Card Holder Name</label>
                <input id="card-holder-name" type="text" class="form-control" placeholder="Enter the name on the card" required>
            </div>

            <div class="form-group mb-4">
                <label for="card-element" class="form-label">Credit or debit card</label>
                <div id="card-element" class="form-control" style="padding: 0.75rem;"></div>
                <div id="card-errors" role="alert" class="text-danger mt-2"></div>
            </div>

            <div class="d-grid">
                <button id="card-button" class="btn btn-primary btn-lg" data-secret="{{ $intent->client_secret }}">
                    Subscribe Now (£{{ number_format($plan->price, 2) }}/{{ $plan->type == 'monthly' ? 'mo' : 'yr' }})
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://js.stripe.com/v3/"></script>
<script>
    // This JavaScript remains the same, it will work with the new form structure.
    const stripe = Stripe('{{ config('services.stripe.key') }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const cardHolderNameInput = document.getElementById('card-holder-name');
    const cardButton = document.getElementById('card-button');
    const clientSecret = cardButton.dataset.secret;
    const paymentForm = document.getElementById('payment-form');

    paymentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        cardButton.disabled = true;
        cardButton.innerHTML = 'Processing... <i class="fas fa-spinner fa-spin"></i>';

        const { setupIntent, error } = await stripe.confirmCardSetup(
            clientSecret, {
                payment_method: {
                    card: cardElement,
                    billing_details: { name: cardHolderNameInput.value }
                }
            }
        );

        if (error) {
            const errorElement = document.getElementById('card-errors');
            errorElement.textContent = error.message;
            cardButton.disabled = false;
            cardButton.innerHTML = 'Subscribe Now (£{{ number_format($plan->price, 2) }}/{{ $plan->type == 'monthly' ? 'mo' : 'yr' }})';
        } else {
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