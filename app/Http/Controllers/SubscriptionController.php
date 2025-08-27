<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrganizationSubscribed;
use App\Notifications\SubscriptionSuccessful;

class SubscriptionController extends Controller
{
    public function checkout(Request $request)
    {
        $plan = Plan::findOrFail($request->query('plan'));
        $user = Auth::user();
        $intent = $user->createSetupIntent();

        // This variable tells the form where to submit for initial signup
        $formActionRoute = route('subscription.store');

        return view('subscription.checkout', compact('plan', 'intent','user', 'formActionRoute'));
    }

   public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|string',
        ]);

        $plan = Plan::find($request->plan_id);
        $user = $request->user();

        try {
            // --- THIS IS THE DEFINITIVE FIX ---
            // 1. Ensure the user exists as a Stripe customer.
            // 2. Add the payment method to the customer.
            // 3. Create the subscription.
            
            // This single line handles creating the customer if they don't exist,
            // updating their payment method, and creating the subscription.
            $user->newSubscription('default', $plan->stripe_price_id)
                 ->create($request->payment_method);
            // --- END OF FIX ---

            $user->status = 'A';
            $user->organization_id = $user->id;
            $user->save();
            
            $superAdmins = User::where('type', 'S')->get();
            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new OrganizationSubscribed($user, $plan));
            }

            // Send confirmation email to the user
            $user->notify(new SubscriptionSuccessful($user, $plan));

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error creating subscription: ' . $e->getMessage()]);
        }

        return redirect()->route('dashboard')->with('success', 'Subscription successful!');
    }
}