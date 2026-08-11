<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StaffController extends Controller
{
    public function index(Business $business)
    {
        $this->authorize('manage', $business);
        return Inertia::render('Business/Staff/Index', [
            'business' => $business,
            'staff' => $business->staff()->get(),
        ]);
    }

    public function store(Request $request, Business $business)
    {
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $business->staff()->create($validated);
        return back()->with('success', 'Staff member added!');
    }

    public function update(Request $request, Business $business, Staff $staff)
    {
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $staff->update($validated);
        return back()->with('success', 'Staff member updated!');
    }

    public function destroy(Business $business, Staff $staff)
    {
        $this->authorize('manage', $business);
        $staff->delete();
        return back()->with('success', 'Staff member removed.');
    }
}
