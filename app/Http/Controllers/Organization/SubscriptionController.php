<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubscriptionController extends Controller
{
    /**
     * Display the organization's current subscription and available plans.
     */
    public function index()
    {
        $organization = Auth::user();
        
        // Eager load the subscription relationship
        $organization->load('subscription');

        // Get all available subscription plans
        $allSubscriptions = Subscription::orderBy('price')->get();

        return view('Organization.subscription.index', [
            'currentSubscription' => $organization->subscription,
            'allSubscriptions' => $allSubscriptions,
        ]);
    }

    /**
     * Update the organization's subscription plan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subscription_id' => ['required', 'integer', Rule::exists('subscriptions', 'id')],
        ]);

        $organization = Auth::user();
        $organization->subscription_id = $request->input('subscription_id');
        $organization->save();

        return redirect()->route('organization.subscription.index')->with('success', 'Subscription plan updated successfully!');
    }
}