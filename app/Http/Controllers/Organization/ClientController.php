<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a list of clients for the logged-in organization.
     */
    public function index(Request $request)
    {
        $query = User::where('type', 'C')
            ->where('organization_id', Auth::id());

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        // ✅ MODIFIED: Set default sort to 'created_at' for "Recents"
        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');
        
        // Whitelist of sortable columns to prevent errors
        if (in_array($sort_by, ['name', 'email', 'status', 'created_at'])) {
            $query->orderBy($sort_by, $sort_order);
        }

        $clients = $query->paginate(10);

        if ($request->ajax()) {
            return view('Organization.clients._clients_table', compact('clients', 'sort_by', 'sort_order'))->render();
        }

        return view('Organization.clients.index', compact('clients', 'sort_by', 'sort_order'));
    }
    
    // ... (The rest of the controller methods remain unchanged)

    /**
     * Display a list of suspended clients.
     */
    public function suspended()
    {
        $clients = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->where('status', 'I') // Only suspended
            ->orderBy('name')
            ->paginate(10);

        return view('organization.clients.suspended', compact('clients'));
    }

    // Show create form
    public function create()
    {
        return view('organization.clients.create');
    }

    // Store new client
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:A,I',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address', 'status']);
        $data['organization_id'] = Auth::id(); // Use logged-in organization's ID
        $data['type'] = 'C';
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('clients', 'public');
            $data['photo'] = $path;
        }

        User::create($data);

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->findOrFail($id);

        return view('organization.clients.edit', compact('client'));
    }

    // Update client
    public function update(Request $request, $id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($client->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:A,I',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $client->name = $request->name;
        $client->email = $request->email;
        $client->phone = $request->phone;
        $client->address = $request->address;
        $client->status = $request->status;

        if ($request->hasFile('photo')) {
            if ($client->photo) {
                Storage::disk('public')->delete($client->photo);
            }
            $path = $request->file('photo')->store('clients', 'public');
            $client->photo = $path;
        }

        if ($request->password) {
            $client->password = Hash::make($request->password);
        }

        $client->save();

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }
    
    public function toggleStatus($id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->findOrFail($id);

        $client->status = $client->status === 'A' ? 'I' : 'A';
        $client->save();

        $message = $client->status === 'A' ? 'Client has been activated.' : 'Client has been suspended.';

        return redirect()->back()->with('success', $message);
    }

    public function destroy($id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::id())
            ->findOrFail($id);

        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}