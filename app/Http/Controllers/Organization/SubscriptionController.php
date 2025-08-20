<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    /**
     * Display the organization's current subscription and history.
     */
    public function index()
    {
        $organization = Auth::user();
        
        // Eager load all subscriptions with their related plan details for efficiency.
        $organization->load('subscriptions.plan');

        // Get all subscriptions for the history tab.
        $allSubscriptions = $organization->subscriptions;

        // Get the current active subscription.
        $currentSubscription = $allSubscriptions->whereNull('ends_at')->first();

        // Get the plan from the current subscription.
        $plan = optional($currentSubscription)->plan;

        return view('Organization.subscription.index', compact('currentSubscription', 'plan', 'allSubscriptions'));
    }
    
    /**
     * --- THIS IS THE NEW METHOD FOR CHANGING PLANS ---
     * Swap the organization's current subscription plan.
     */
    public function swap(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')],
        ]);

        $organization = Auth::user();
        $newPlan = Plan::find($request->input('plan_id'));

        try {
            // Use Cashier's swap method. It handles proration automatically.
            $organization->subscription('default')->swap($newPlan->stripe_price_id);
            
            return redirect()->route('organization.subscription.index')->with('success', 'Subscription plan changed successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => 'Error changing subscription: ' . $e->getMessage()]);
        }
    }
}