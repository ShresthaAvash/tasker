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
        
        $recentRequests = User::where('type', 'O')->where('status', 'R')->latest()->take(5)->get();

        return view('SuperAdmin.dashboard', compact(
            'organizationCount',
            'subscriptionPlansCount',
            'subscribedOrgsCount',
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
    
    public function subscribedOrganizations(Request $request)
    {
        $status = $request->get('status', 'active'); // Default to the 'active' tab

        $query = User::where('type', 'O')
            ->whereHas('subscriptions', function ($q) use ($status) {
                // --- THIS IS THE CRITICAL FIX ---
                if ($status === 'active') {
                    // An "active" subscription is one that is NOT canceled.
                    // This correctly excludes subscriptions in their grace period.
                    $q->whereNull('ends_at');
                } else { // deactivated
                    // A "deactivated" subscription is one that IS canceled.
                    $q->whereNotNull('ends_at');
                }
            })
            ->with(['subscriptions' => fn($q) => $q->orderBy('created_at', 'desc'), 'subscriptions.plan']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
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