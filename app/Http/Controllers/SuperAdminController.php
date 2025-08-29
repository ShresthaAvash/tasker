<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscription;
use App\Models\Plan;
use App\Models\AssignedTask; // Import AssignedTask model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class SuperAdminController extends Controller
{
    /**
     * --- THIS IS THE DEFINITIVE FIX for all earning calculations ---
     * This function now accurately calculates revenue based on one active plan per organization.
     */
    private function calculateEarnings()
    {
        $monthlyTotal = 0;
        $yearlyTotal = 0;

        // Get all organizations that have an active subscription
        $subscribedOrgs = User::where('type', 'O')
            ->whereHas('subscriptions', function ($query) {
                $query->where('stripe_status', 'active');
            })
            ->with(['subscriptions' => function ($query) {
                $query->where('stripe_status', 'active')->with('plan');
            }])
            ->get();

        foreach ($subscribedOrgs as $org) {
            // An organization should only have one active subscription at a time.
            // We take the most recent one to be safe.
            $subscription = $org->subscriptions->first();
            if ($subscription && $subscription->plan) {
                if ($subscription->plan->type === 'monthly') {
                    $monthlyTotal += $subscription->plan->price;
                } elseif ($subscription->plan->type === 'annually') {
                    $yearlyTotal += $subscription->plan->price;
                }
            }
        }

        return [
            'monthly' => $monthlyTotal,
            'yearly' => $yearlyTotal,
        ];
    }

    /**
     * Display the dashboard with stats.
     */
    public function dashboard()
    {
        $organizationCount = User::where('type', 'O')->count();
        $subscriptionPlansCount = Plan::count();
        $subscribedOrgsCount = User::where('type', 'O')
            ->whereHas('subscriptions', function ($query) {
                $query->where('stripe_status', 'active')->orWhere('stripe_status', 'trialing');
            })
            ->count();
            
        // Use the corrected calculation
        $earnings = $this->calculateEarnings();
        $totalEarnings = $earnings['monthly'] + $earnings['yearly'];

        // --- THIS IS THE MODIFIED LOGIC ---
        // Get the 5 most recently joined (created) organizations with an active subscription
        // Eager load the 'subscriptions' relationship to prevent N+1 queries in the view.
        $recentlyJoined = User::where('type', 'O')
            ->whereHas('subscriptions', fn($q) => $q->where('stripe_status', 'active'))
            ->with('subscriptions') // <-- EAGER LOADING ADDED
            ->latest() // Orders by created_at descending
            ->take(5)
            ->get();
        // --- END OF MODIFICATION ---

        // Removed chart data calculation as per user request.


        return view('SuperAdmin.dashboard', compact(
            'organizationCount',
            'subscriptionPlansCount',
            'subscribedOrgsCount',
            'recentlyJoined', // Pass the new variable
            'totalEarnings'
            // Removed chartLabels and chartData from compact
        ));
    }

    /**
     * Display the earnings report page.
     */
    public function earnings(Request $request)
    {
        // Use the corrected calculation
        $earnings = $this->calculateEarnings();
        $totalRevenue = $earnings['monthly'] + $earnings['yearly'];

        $totalSubscriptionCount = User::where('type', 'O')
                                      ->whereHas('subscriptions', fn($q) => $q->where('stripe_status', 'active'))
                                      ->count();

        $type = $request->get('type', 'total');
        
        $title = match ($type) {
            'monthly' => 'Monthly Subscriptions',
            'annually' => 'Yearly Subscriptions',
            default => 'All Active Subscriptions',
        };

        $query = User::where('type', 'O')
            ->whereHas('subscriptions', function ($q) use ($type) {
                $q->where('stripe_status', 'active');
                
                if (in_array($type, ['monthly', 'annually'])) {
                    $q->whereHas('plan', function ($planQuery) use ($type) {
                        $planQuery->where('type', $type);
                    });
                }
            })
            ->with(['subscriptions' => function($q) {
                $q->where('stripe_status', 'active')->with('plan');
            }]);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $organizations = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return view('SuperAdmin.earnings._table', compact('organizations'))->render();
        }

        return view('SuperAdmin.earnings.index', [
            'monthlyEarnings' => $earnings['monthly'],
            'yearlyEarnings' => $earnings['yearly'],
            'totalRevenue' => $totalRevenue,
            'totalSubscriptionCount' => $totalSubscriptionCount,
            'organizations' => $organizations,
            'title' => $title,
        ]);
    }

    // List all organizations
    public function index(Request $request)
    {
        $query = User::where('type', 'O')->with('subscriptions');
        
        $statuses = $request->get('statuses');
        if (!empty($statuses) && is_array($statuses)) {
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');
        
        if (in_array($sort_by, ['name', 'email', 'status', 'created_at'])) {
            $query->orderBy($sort_by, $sort_order);
        }

        $organizations = $query->paginate(10);

        if ($request->ajax()) {
            return view('SuperAdmin._organizations_table', compact('organizations', 'sort_by', 'sort_order'))->render();
        }

        return view('SuperAdmin.index', compact('organizations', 'sort_by', 'sort_order'));
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
        $organization = User::where('type', 'O')->with('subscriptions.plan')->findOrFail($id);
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

    // Toggle status (active/inactive)
    public function destroy($id)
    {
        $organization = User::where('type', 'O')->findOrFail($id);
        $organization->status = $organization->status === 'A' ? 'I' : 'A';
        $organization->save();

        $message = $organization->status === 'A' ? "Organization '{$organization->name}' has been activated." : "Organization '{$organization->name}' has been made inactive.";

        return redirect()->route('superadmin.organizations.index')->with('success', $message);
    }
    
    public function subscribedOrganizations(Request $request)
    {
        $status = $request->get('status', 'active'); // Default to the 'active' tab

        $query = User::where('type', 'O')
            ->whereHas('subscriptions', function ($q) use ($status) {
                if ($status === 'active') {
                    $q->whereNull('ends_at');
                } else { // deactivated
                    $q->whereNotNull('ends_at');
                }
            })
            ->with(['subscriptions' => fn($q) => $q->orderBy('created_at', 'desc'), 'subscriptions.plan']);

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }
        
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');

        if ($sort_by === 'ends_at') {
            $subQuery = Subscription::selectRaw('COALESCE(current_period_end, ends_at)')
                ->whereColumn('subscriptions.user_id', 'users.id')
                ->latest()
                ->limit(1);
            $query->orderBy($subQuery, $sort_order);
        } elseif (in_array($sort_by, ['name', 'created_at'])) {
            $query->orderBy($sort_by, $sort_order);
        }

        $organizations = $query->paginate(10);
        
        if ($request->ajax()) {
            return view('SuperAdmin.subscriptions._subscribed_table', compact('organizations', 'sort_by', 'sort_order'))->render();
        }

        return view('SuperAdmin.subscriptions.subscribed', compact('organizations', 'sort_by', 'sort_order'));
    }

    public function cancelSubscription(User $user)
    {
        try {
            $user->subscription('default')->cancel();
            return redirect()->back()->with('success', "Subscription for {$user->name} has been scheduled for cancellation.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Could not cancel a subscription. ' . $e->getMessage());
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
    
    /**
     * --- THIS IS THE NEW METHOD ---
     * Display the subscription history for a specific organization.
     */
    public function subscriptionHistory(User $user)
    {
        if ($user->type !== 'O') {
            abort(404, 'User is not an organization.');
        }

        // Eager load the plan relationship on each subscription
        $subscriptions = $user->subscriptions()->with('plan')->get();

        return view('SuperAdmin.subscriptions.history', [
            'organization' => $user,
            'subscriptions' => $subscriptions,
        ]);
    }
};