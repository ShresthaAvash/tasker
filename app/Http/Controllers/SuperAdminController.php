<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SuperAdminController extends Controller
{
    /**
     * Display the dashboard with stats.
     */
    public function dashboard()
    {
        $organizationCount = User::where('users.type', 'O')->count();
        $subscriptionPlansCount = Subscription::count();
        $subscribedOrgsCount = User::where('users.type', 'O')->whereNotNull('subscription_id')->count();

        // Calculate Estimated Monthly Earnings
        $monthlyEarnings = User::where('users.type', 'O')
            ->whereHas('subscription', fn($q) => $q->where('type', 'monthly'))
            ->join('subscriptions', 'users.subscription_id', '=', 'subscriptions.id')
            ->sum('subscriptions.price');

        $annualEarnings = User::where('users.type', 'O')
            ->whereHas('subscription', fn($q) => $q->where('type', 'annually'))
            ->join('subscriptions', 'users.subscription_id', '=', 'subscriptions.id')
            ->sum('subscriptions.price');

        $totalMonthlyEarnings = $monthlyEarnings + ($annualEarnings / 12);
        
        $recentRequests = User::where('type', 'O')->where('status', 'R')->latest()->take(5)->get();

        return view('SuperAdmin.dashboard', compact(
            'organizationCount',
            'subscriptionPlansCount',
            'subscribedOrgsCount',
            'totalMonthlyEarnings',
            'recentRequests'
        ));
    }

    // List all organizations
    public function index(Request $request)
    {
        // Eager load the subscription relationship provided by Cashier
        $query = User::where('type', 'O')->with('subscriptions');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%");
        }

        $organizations = $query->orderBy('name')->paginate(10);

        return view('SuperAdmin.index', compact('organizations'));
    }
    
    // Show create form
    public function create()
    {
        return view('SuperAdmin.create');
    }

    // Store new organization
    public function store(Request $request)
    {
        $id = $request->input('id');
        $user = empty($id) ? new User() : User::findOrFail($id);
        $user->name     = $request->input('name');
        $user->email    = $request->input('email');
        $user->phone    = $request->input('phone');
        $user->address  = $request->input('address');

        if (empty($id) || $request->filled('password')) {
            $request->validate(['password' => 'required|string|min:6|confirmed']);
            $user->password = bcrypt($request->input('password'));
        }

        $user->type   = 'O';
        $user->status = 'A';
        $user->save();
        
        if (empty($id)) {
            $user->organization_id = $user->id;
            $user->save();
        }

        return redirect()->route('superadmin.organizations.index')->with('success', 'Organization saved successfully!');
    }

    // Show details
    public function show($id)
    {
        $organization = User::where('type', 'O')->with('subscriptions')->findOrFail($id);
        return view('SuperAdmin.view', compact('organization'));
    }

    // Show edit form
    public function edit($id)
    {
        $user = User::where('type', 'O')->findOrFail($id);
        return view('SuperAdmin.edit', compact('user'));
    }

    // Update organization
    public function update(Request $request, $id)
    {
        $organization = User::where('type', 'O')->findOrFail($id);
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $organization->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
        $organization->update($request->all());
        return redirect()->route('superadmin.organizations.index')->with('success', 'Organization updated successfully.');
    }

    // Toggle status (active/suspended)
    public function destroy($id)
    {
        $organization = User::where('type', 'O')->findOrFail($id);
        $organization->status = $organization->status === 'A' ? 'I' : 'A';
        $organization->save();

        return redirect()->route('superadmin.organizations.index')->with('success', 'Organization status updated.');
    }

    public function subscriptionRequests()
    {
        $requestedOrganizations = User::where('type', 'O')
            ->where('status', 'R')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('SuperAdmin.subscriptions.requests', compact('requestedOrganizations'));
    }

    public function activeSubscriptions()
    {
        $organizations = User::where('type', 'O')
            ->whereHas('subscriptions', function ($query) {
                $query->where('stripe_status', 'active');
            })
            // --- THIS IS THE CHANGE ---
            ->with('subscriptions.plan') // Eager-load the plan relationship
            ->latest()
            ->paginate(10);

        return view('SuperAdmin.subscriptions.active', compact('organizations'));
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

    public function cancelSubscription(User $user)
    {
        try {
            $user->subscription('default')->cancel();
            return redirect()->back()->with('success', "Subscription for {$user->name} has been scheduled for cancellation.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not cancel subscription. ' . $e->getMessage());
        }
    }

    public function resumeSubscription(User $user)
    {
        try {
            $user->subscription('default')->resume();
            return redirect()->back()->with('success', "Subscription for {$user->name} has been resumed.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not resume subscription. ' . $e->getMessage());
        }
    }
};