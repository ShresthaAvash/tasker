<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\StaffDesignation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StaffDesignationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $designations = StaffDesignation::where('organization_id', Auth::id())
            ->orderBy('name')
            ->paginate(10);
            
        return view('organization.staff_designations.index', compact('designations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('organization.staff_designations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('staff_designations')->where('organization_id', Auth::id()),
            ],
        ]);

        StaffDesignation::create([
            'name' => $request->name,
            'organization_id' => Auth::id(),
        ]);

        return redirect()->route('staff-designations.index')->with('success', 'Staff designation created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StaffDesignation $staffDesignation)
    {
        // Ensure the organization owns this designation
        if ($staffDesignation->organization_id !== Auth::id()) {
            abort(403);
        }

        return view('organization.staff_designations.edit', compact('staffDesignation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StaffDesignation $staffDesignation)
    {
        // Ensure the organization owns this designation
        if ($staffDesignation->organization_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('staff_designations')->where('organization_id', Auth::id())->ignore($staffDesignation->id),
            ],
        ]);

        $staffDesignation->update(['name' => $request->name]);

        return redirect()->route('staff-designations.index')->with('success', 'Staff designation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StaffDesignation $staffDesignation)
    {
        // Ensure the organization owns this designation
        if ($staffDesignation->organization_id !== Auth::id()) {
            abort(403);
        }

        // Optional: Before deleting, you might want to un-assign this designation from any staff members.
        // User::where('staff_designation_id', $staffDesignation->id)->update(['staff_designation_id' => null]);

        $staffDesignation->delete();

        return redirect()->route('staff-designations.index')->with('success', 'Staff designation deleted successfully.');
    }
}