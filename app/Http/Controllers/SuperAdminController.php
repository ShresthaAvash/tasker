<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    // List all organizations
    public function index(Request $request)
    {
        $query = User::where('type', 'O');

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

        // Check if we're creating or updating
        $user = empty($id) ? new User() : User::findOrFail($id);

        // Assign values manually
        $user->name     = $request->input('name');
        $user->email    = $request->input('email');
        $user->phone    = $request->input('phone');
        $user->address  = $request->input('address');

        // If creating a new user or changing password
        if (empty($id) || $request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:6|confirmed'
            ]);
            $user->password = bcrypt($request->input('password'));
        }

        // Set type and status
        $user->type   = 'O';  // ✅ Mark as Organization
        $user->status = 'A';  // Active by default

        $user->save();
        
        // ✅ ADDED: If creating, set organization_id to the new user's own ID
        if (empty($id)) {
            $user->organization_id = $user->id;
            $user->save();
        }

        return redirect()->route('superadmin.organizations.index')->with('success', 'Organization saved successfully!');
    }

    // Show details
    public function show($id)
    {
        $organization = User::where('type', 'O')->findOrFail($id);
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

        $organization->update([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
        ]);

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
};