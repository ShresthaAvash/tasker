<?php
 
namespace App\Http\Controllers\Organization;
 
use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class SubscriptionController extends Controller
{
    public function index()
    {
        $organization = Auth::user();
 
        $currentSubscription = $organization->subscription('default');
        $plan = optional($currentSubscription)->plan;
 
        $invoices = [];
        if ($organization->hasStripeId()) {
            $invoices = $organization->invoices();
        }
 
        return view('Organization.subscription.index', compact('currentSubscription', 'plan', 'invoices'));
    }
 
    /**
     * No proration, charge full yearly immediately, then we handle
     * 'Ends On' carry-over in the view from billing history.
     */
    public function processChangePlan(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'payment_method' => 'required|string',
        ]);
 
        $plan = Plan::findOrFail($request->plan_id);
        $user = $request->user();
 
        try {
            // Ensure the PM is set as default to avoid incomplete payments
            if ($request->filled('payment_method')) {
                $user->updateDefaultPaymentMethod($request->payment_method);
            }
 
            // Full charge now, no proration credits/deductions.
            // (swapAndInvoice exists in Cashier and invoices immediately)
            $user->subscription('default')
                ->noProrate()
                ->swapAndInvoice($plan->stripe_price_id);
 
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error changing subscription: ' . $e->getMessage()]);
        }
 
        return redirect()
            ->route('organization.subscription.index')
            ->with('success', 'Subscription plan changed successfully! Full yearly charge applied and remaining time will be added to the Ends On date.');
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
}