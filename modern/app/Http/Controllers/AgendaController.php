<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Business;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgendaController extends Controller
{
    public function index(Request $request, Business $business)
    {
        $this->authorize('manage', $business);

        $date = $request->get('date', now()->toDateString());

        $appointments = $business->appointments()
            ->with(['service', 'contact', 'staff'])
            ->whereDate('start_at', $date)
            ->orderBy('start_at')
            ->get();

        return Inertia::render('Business/Agenda/Index', [
            'business' => $business,
            'appointments' => $appointments,
            'date' => $date,
        ]);
    }

    public function calendar(Business $business)
    {
        $this->authorize('manage', $business);

        $appointments = $business->appointments()
            ->with(['service', 'contact'])
            ->where('start_at', '>=', now()->subMonth())
            ->where('start_at', '<=', now()->addMonths(2))
            ->get()
            ->map(function ($apt) {
                return [
                    'id' => $apt->id,
                    'title' => ($apt->contact->firstname ?? 'Guest') . ' - ' . ($apt->service->name ?? ''),
                    'start' => $apt->start_at->toIso8601String(),
                    'end' => $apt->end_at->toIso8601String(),
                    'color' => $apt->service->color ?? '#3B82F6',
                    'status' => $apt->status->value,
                ];
            });

        return Inertia::render('Business/Agenda/Calendar', [
            'business' => $business,
            'events' => $appointments,
        ]);
    }

    public function action(Request $request, Business $business, Appointment $appointment, BookingService $bookingService)
    {
        $this->authorize('manage', $business);

        $action = $request->validate(['action' => 'required|in:confirm,cancel,serve'])['action'];

        try {
            match ($action) {
                'confirm' => $bookingService->confirm($appointment),
                'cancel' => $bookingService->cancel($appointment),
                'serve' => $bookingService->serve($appointment),
            };
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Appointment ' . $action . 'ed!');
    }
}
