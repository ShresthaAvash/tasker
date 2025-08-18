<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::latest()->paginate(10);
        return view('SuperAdmin.subscriptions.index', compact('subscriptions'));
    }

    public function create()
    {
        return view('SuperAdmin.subscriptions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,annually',
        ]);

        Subscription::create($request->all());

        return redirect()->route('superadmin.subscriptions.index')->with('success', 'Subscription created successfully.');
    }

    public function edit(Subscription $subscription)
    {
        return view('SuperAdmin.subscriptions.edit', compact('subscription'));
    }

    public function update(Request $request, Subscription $subscription)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,annually',
        ]);

        $subscription->update($request->all());

        return redirect()->route('superadmin.subscriptions.index')->with('success', 'Subscription updated successfully.');
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->delete();
        return redirect()->route('superadmin.subscriptions.index')->with('success', 'Subscription deleted successfully.');
    }
    
    /**
     * Display a list of pending subscription requests.
     */
    public function subscriptionRequests()
    {
        $requestedOrganizations = User::where('type', 'O')
            ->where('status', 'R')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('SuperAdmin.subscriptions.requests', compact('requestedOrganizations'));
    }

    /**
     * Approve a subscription request.
     */
    public function approveSubscription(User $user)
    {
        if ($user->type === 'O' && $user->status === 'R') {
            $user->status = 'A'; // Set status to Active
            $user->save();
            return redirect()->route('superadmin.subscriptions.requests')->with('success', 'Organization has been activated successfully.');
        }

        return redirect()->route('superadmin.subscriptions.requests')->with('error', 'Invalid request.');
    }
}