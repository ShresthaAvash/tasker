<?php
namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    // Show list of clients for logged-in org
    public function index()
    {
        $clients = User::where('type', 'C')
            ->where('organization_id', Auth::user()->organization_id)
            ->get();

        return view('organization.clients.index', compact('clients'));
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
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:A,I',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address', 'status']);
        $data['organization_id'] = Auth::user()->organization_id;
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
            ->where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        return view('organization.clients.edit', compact('client'));
    }

    // Update client
    public function update(Request $request, $id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$client->id,
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
            // Delete old photo if exists
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

    // Optional: delete method if needed
    public function destroy($id)
    {
        $client = User::where('type', 'C')
            ->where('organization_id', Auth::user()->organization_id)
            ->findOrFail($id);

        if ($client->photo) {
            Storage::disk('public')->delete($client->photo);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
