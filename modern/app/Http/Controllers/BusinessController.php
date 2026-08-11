<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BusinessController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('create', Business::class);

        $businesses = $request->user()->ownedBusinesses()->withCount(['services', 'contacts', 'appointments'])->get();
        return Inertia::render('Business/Index', ['businesses' => $businesses]);
    }

    public function create()
    {
        $this->authorize('create', Business::class);
        return Inertia::render('Business/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Business::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'timezone' => 'required|string',
            'strategy' => 'required|in:timeslot,dateslot',
            'phone' => 'nullable|string|max:30',
            'postal_address' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $count = Business::where('slug', $validated['slug'])->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . ($count + 1);
        }

        $business = Business::create($validated);
        $business->owners()->attach($request->user()->id, ['role' => 'owner']);

        return redirect()->route('businesses.show', $business)->with('success', 'Business created!');
    }

    public function show(Business $business)
    {
        $this->authorize('manage', $business);

        $business->load(['services', 'staff']);

        $stats = [
            'total_appointments' => $business->appointments()->count(),
            'upcoming' => $business->appointments()->where('start_at', '>=', now())->whereNot('status', 'canceled')->count(),
            'contacts' => $business->contacts()->count(),
            'services' => $business->services()->count(),
        ];

        $recentAppointments = $business->appointments()
            ->with(['service', 'contact'])
            ->orderByDesc('start_at')
            ->limit(10)
            ->get();

        return Inertia::render('Business/Show', [
            'business' => $business,
            'stats' => $stats,
            'recentAppointments' => $recentAppointments,
        ]);
    }

    public function edit(Business $business)
    {
        $this->authorize('update', $business);
        return Inertia::render('Business/Edit', ['business' => $business]);
    }

    public function update(Request $request, Business $business)
    {
        $this->authorize('update', $business);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'timezone' => 'required|string',
            'strategy' => 'required|in:timeslot,dateslot',
            'phone' => 'nullable|string|max:30',
            'postal_address' => 'nullable|string',
        ]);

        $business->update($validated);
        return redirect()->route('businesses.show', $business)->with('success', 'Business updated!');
    }

    public function destroy(Business $business)
    {
        $this->authorize('delete', $business);
        $business->delete();
        return redirect()->route('businesses.index')->with('success', 'Business deleted.');
    }
}
