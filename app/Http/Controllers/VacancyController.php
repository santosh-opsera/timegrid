<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VacancyController extends Controller
{
    public function index(Business $business)
    {
        $this->authorize('manage', $business);

        $vacancies = $business->vacancies()
            ->with(['service', 'staff'])
            ->where('date', '>=', now()->toDateString())
            ->orderBy('date')
            ->orderBy('start_at')
            ->get();

        return Inertia::render('Business/Vacancies/Index', [
            'business' => $business,
            'vacancies' => $vacancies,
            'services' => $business->services()->get(),
            'staff' => $business->staff()->get(),
        ]);
    }

    public function store(Request $request, Business $business)
    {
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:humanresources,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'capacity' => 'integer|min:1|max:100',
        ]);

        $business->vacancies()->create([
            'service_id' => $validated['service_id'],
            'humanresource_id' => $validated['staff_id'] ?? null,
            'date' => $validated['date'],
            'start_at' => $validated['date'].' '.$validated['start_time'].':00',
            'finish_at' => $validated['date'].' '.$validated['end_time'].':00',
            'capacity' => $validated['capacity'] ?? 1,
        ]);

        return back()->with('success', 'Vacancy created!');
    }

    public function bulkStore(Request $request, Business $business)
    {
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'staff_id' => 'nullable|exists:humanresources,id',
            'dates' => 'required|array|min:1',
            'dates.*' => 'date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        foreach ($validated['dates'] as $date) {
            $business->vacancies()->create([
                'service_id' => $validated['service_id'],
                'humanresource_id' => $validated['staff_id'] ?? null,
                'date' => $date,
                'start_at' => $date.' '.$validated['start_time'].':00',
                'finish_at' => $date.' '.$validated['end_time'].':00',
            ]);
        }

        return back()->with('success', count($validated['dates']).' vacancies created!');
    }

    public function destroy(Business $business, Vacancy $vacancy)
    {
        $this->authorize('manage', $business);
        $vacancy->delete();

        return back()->with('success', 'Vacancy removed.');
    }
}
