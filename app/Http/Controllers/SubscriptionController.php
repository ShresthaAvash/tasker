<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\OrganizationSubscribed;

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
            $user->newSubscription('default', $plan->stripe_price_id)
                ->create($request->payment_method);

            $user->status = 'A';
            $user->organization_id = $user->id;
            $user->save();
            
            $superAdmins = User::where('type', 'S')->get();
            if ($superAdmins->isNotEmpty()) {
                Notification::send($superAdmins, new OrganizationSubscribed($user, $plan));
            }
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error creating subscription: ' . $e->getMessage()]);
        }

        return redirect()->route('dashboard')->with('success', 'Subscription successful!');
    }
}