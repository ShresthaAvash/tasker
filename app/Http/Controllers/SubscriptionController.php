<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User; // ADD THIS LINE
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification; // ADD THIS LINE
use App\Notifications\OrganizationSubscribed; // ADD THIS LINE

class SubscriptionController extends Controller
{
    /**
     * Show the subscription checkout page.
     */
    public function checkout(Request $request)
{
    $plan = Plan::findOrFail($request->query('plan')); // <-- You changed this, which is good!
    $user = Auth::user();

    $intent = $user->createSetupIntent();

    // You are correctly passing 'plan' and 'intent' here
    return view('subscription.checkout', compact('plan', 'intent','user'));
}

    /**
     * Process the subscription payment.
     */
   public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id', // <-- Changed to plans
            'payment_method' => 'required|string',
        ]);

        $plan = Plan::find($request->plan_id); // <-- Changed to Plan
        $user = $request->user();

        try {
            // Create the subscription
            $user->newSubscription('default', $plan->stripe_price_id)
                ->create($request->payment_method);

            // Update the user's status to Active
            $user->status = 'A';
            $user->organization_id = $user->id; // Set organization ID
            $user->save();
            
            // --- THIS IS THE NEW NOTIFICATION LOGIC ---
            $superAdmins = User::where('type', 'S')->get();
            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new OrganizationSubscribed($user, $plan));
            }
            // --- END OF NEW LOGIC ---

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error creating subscription: ' . $e->getMessage()]);
        }

        return redirect()->route('dashboard')->with('success', 'Subscription successful!');
    }
}