<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan; // <-- Use the Plan model
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Price;
use Stripe\Product;

class PlanController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function index()
    {
        // Use the Plan model
        $plans = Plan::latest()->paginate(10);
        return view('SuperAdmin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('SuperAdmin.plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,annually',
        ]);

        // Create a product in Stripe
        $stripeProduct = Product::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Create a price in Stripe
        $stripePrice = Price::create([
            'product' => $stripeProduct->id,
            'unit_amount' => $request->price * 100, // Price in cents
            'currency' => config('cashier.currency'),
            'recurring' => ['interval' => $request->type === 'monthly' ? 'month' : 'year'],
        ]);

        // Create a Plan record, not a Subscription record
        Plan::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'type' => $request->type,
            'stripe_price_id' => $stripePrice->id,
        ]);

        return redirect()->route('superadmin.plans.index')->with('success', 'Subscription plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('SuperAdmin.plans.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,annually',
        ]);

        try {
            // --- THIS IS THE DEFINITIVE FIX ---
            $oldPrice = Price::retrieve($plan->stripe_price_id);
            $productId = $oldPrice->product;

            // Always update the product's name and description in Stripe
            Product::update($productId, [
                'name' => $request->name,
                'description' => $request->description,
            ]);
            
            // Check if the price or type (interval) has changed
            if ($plan->price != $request->price || $plan->type != $request->type) {
                // 1. Create a new Price in Stripe
                $newStripePrice = Price::create([
                    'product' => $productId,
                    'unit_amount' => $request->price * 100,
                    'currency' => config('cashier.currency'),
                    'recurring' => ['interval' => $request->type === 'monthly' ? 'month' : 'year'],
                ]);

                // 2. Archive the old Price in Stripe
                Price::update($plan->stripe_price_id, ['active' => false]);
                
                // 3. Update the local plan with all new details, including the new price ID
                $plan->stripe_price_id = $newStripePrice->id;
                $plan->price = $request->price;
                $plan->type = $request->type;
            }

            // Update name and description on the local model regardless
            $plan->name = $request->name;
            $plan->description = $request->description;
            $plan->save();

            // --- END OF FIX ---

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not update plan in Stripe: ' . $e->getMessage());
        }

        return redirect()->route('superadmin.plans.index')->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        try {
            // Instead of deleting, we will make the price inactive in Stripe.
            // This prevents new subscriptions but preserves history for existing ones.
            Price::update($plan->stripe_price_id, ['active' => false]);

            // Now, delete the local record.
            $plan->delete();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not deactivate plan in Stripe: ' . $e->getMessage());
        }
        
        return redirect()->route('superadmin.plans.index')->with('success', 'Subscription plan deleted successfully.');
    }
}