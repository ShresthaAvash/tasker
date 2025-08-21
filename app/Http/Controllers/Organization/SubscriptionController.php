<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    public function index()
    {
        $organization = Auth::user();
        $organization->load('subscriptions.plan');

        // --- THIS IS THE DEFINITIVE FIX FOR HISTORY ---
        // We get ALL subscriptions, including those that have ended.
        $allSubscriptions = $organization->subscriptions()->with('plan')->get();
        
        $currentSubscription = $allSubscriptions->whereNull('ends_at')->first();
        $plan = optional($currentSubscription)->plan;

        return view('Organization.subscription.index', compact('currentSubscription', 'plan', 'allSubscriptions'));
    }

    public function showChangePlanForm(Request $request)
    {
        $request->validate(['plan' => 'required|exists:plans,id']);
        $plan = Plan::findOrFail($request->query('plan'));
        $user = Auth::user();
        $intent = $user->createSetupIntent();
        
        $formActionRoute = route('organization.subscription.change.process');

        return view('subscription.checkout', compact('plan', 'intent', 'user', 'formActionRoute'));
    }

    public function processChangePlan(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|string',
        ]);

        $plan = Plan::find($request->plan_id);
        $user = $request->user();

        try {
            $user->updateDefaultPaymentMethod($request->payment_method);
            $user->subscription('default')->swapAndInvoice($plan->stripe_price_id);
        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('organization.subscription.index')]
            );
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error changing subscription: ' . $e->getMessage()]);
        }

        return redirect()->route('organization.subscription.index')->with('success', 'Subscription plan changed successfully!');
    }
}