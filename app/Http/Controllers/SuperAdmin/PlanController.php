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
        ]);

        $plan->update($request->only(['name', 'description']));

        return redirect()->route('superadmin.plans.index')->with('success', 'Subscription plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();
        return redirect()->route('superadmin.plans.index')->with('success', 'Subscription plan deleted successfully.');
    }
}