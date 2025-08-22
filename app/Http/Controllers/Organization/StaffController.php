<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StaffDesignation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of the staff members.
     */
    public function index(Request $request)
    {
        $designations = StaffDesignation::where('organization_id', Auth::id())->orderBy('name')->get();

        $query = User::where('type', 'T')
            ->where('organization_id', Auth::id())
            ->with('designation');
            
        // --- THIS IS THE FIX: Filter by an array of statuses from the request ---
        $statuses = $request->get('statuses');
        if (!empty($statuses) && is_array($statuses)) {
             // We use whereIn to filter by multiple statuses at once
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('designation_id')) {
            $query->where('staff_designation_id', $request->designation_id);
        }

        $sort_by = $request->get('sort_by', 'created_at');
        $sort_order = $request->get('sort_order', 'desc');
        if (in_array($sort_by, ['name', 'email', 'status', 'created_at'])) {
            $query->orderBy($sort_by, $sort_order);
        }

        $staff = $query->paginate(10);
        
        if ($request->ajax()) {
            return view('Organization.staff._staff_table', compact('staff', 'sort_by', 'sort_order'))->render();
        }
        
        return view('Organization.staff.index', compact('staff', 'sort_by', 'sort_order', 'designations'));
    }
    
    /**
     * Toggle the status of a staff member between Active and Inactive.
     */
    public function toggleStatus(User $staff)
    {
        if ($staff->organization_id !== Auth::id() || $staff->type !== 'T') {
            abort(403);
        }

        $staff->status = $staff->status === 'A' ? 'I' : 'A';
        $staff->save();

        // --- THIS IS THE FIX: Updated wording ---
        $message = $staff->status === 'A' ? 'Staff member has been activated.' : 'Staff member has been made inactive.';

        return redirect()->back()->with('success', $message);
    }
    
    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        $designations = StaffDesignation::where('organization_id', Auth::id())->orderBy('name')->get();
        return view('organization.staff.create', compact('designations'));
    }

    /**
     * Store a newly created staff member in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')],
            'staff_designation_id' => ['nullable', 'integer', Rule::exists('staff_designations', 'id')->where('organization_id', Auth::id())],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:A,I',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $data = $request->all();
        $data['organization_id'] = Auth::id();
        $data['type'] = 'T'; // 'T' for Team/Staff
        $data['password'] = Hash::make($request->password);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('staff', 'public');
        }

        User::create($data);

        return redirect()->route('staff.index')->with('success', 'Staff member created successfully.');
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(User $staff)
    {
        if ($staff->organization_id !== Auth::id() || $staff->type !== 'T') {
            abort(403);
        }
        
        $designations = StaffDesignation::where('organization_id', Auth::id())->orderBy('name')->get();
        return view('organization.staff.edit', compact('staff', 'designations'));
    }

    /**
     * Update the specified staff member in storage.
     */
    public function update(Request $request, User $staff)
    {
        if ($staff->organization_id !== Auth::id() || $staff->type !== 'T') {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
            'staff_designation_id' => ['nullable', 'integer', Rule::exists('staff_designations', 'id')->where('organization_id', Auth::id())],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'photo' => 'nullable|image|max:2048',
            'status' => 'required|in:A,I',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $data = $request->except('password', 'photo');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            if ($staff->photo) {
                Storage::disk('public')->delete($staff->photo);
            }
            $data['photo'] = $request->file('photo')->store('staff', 'public');
        }

        $staff->update($data);

        return redirect()->route('staff.index')->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified staff member from storage.
     */
    public function destroy(User $staff)
    {
        if ($staff->organization_id !== Auth::id() || $staff->type !== 'T') {
            abort(403);
        }

        if ($staff->photo) {
            Storage::disk('public')->delete($staff->photo);
        }

        $staff->delete();

        return redirect()->route('staff.index')->with('success', 'Staff member deleted successfully.');
    }
}