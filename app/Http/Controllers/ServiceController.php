<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index(Business $business)
    {
        $this->authorize('manage', $business);
        $services = $business->services()->get();
        return Inertia::render('Business/Services/Index', [
            'business' => $business,
            'services' => $services,
        ]);
    }

    public function store(Request $request, Business $business)
    {
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:5|max:480',
            'color' => 'nullable|string|max:20',
        ]);

        $business->services()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? '',
            'duration' => $validated['duration'],
            'color' => $validated['color'] ?? null,
        ]);
        return back()->with('success', 'Service created!');
    }

    public function update(Request $request, Business $business, Service $service)
    {
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'required|integer|min:5|max:480',
            'color' => 'nullable|string|max:20',
        ]);

        $service->update($validated);
        return back()->with('success', 'Service updated!');
    }

    public function destroy(Business $business, Service $service)
    {
        $this->authorize('manage', $business);
        $service->delete();
        return back()->with('success', 'Service deleted.');
    }
}
