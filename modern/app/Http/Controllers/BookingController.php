<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Business;
use App\Models\Contact;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function show(Business $business)
    {
        $business->load(['services' => function ($q) {
            $q->where('is_active', true);
        }]);

        return Inertia::render('Booking/Show', [
            'business' => $business,
        ]);
    }

    public function store(Request $request, Business $business, BookingService $bookingService)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer',
            'date' => 'required|date|after_or_equal:today',
            'time' => 'required|date_format:H:i',
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'comments' => 'nullable|string|max:1000',
        ]);

        $service = $business->services()->where('is_active', true)->findOrFail($validated['service_id']);

        $user = $request->user();

        $contact = Contact::firstOrCreate(
            ['business_id' => $business->id, 'email' => $validated['email']],
            [
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'] ?? '',
                'phone' => $validated['phone'] ?? null,
                'user_id' => $user->id,
            ]
        );

        if (!$contact->user_id) {
            $contact->update(['user_id' => $user->id]);
        }

        try {
            $appointment = $bookingService->book($business, $service, $contact, $validated['date'], $validated['time'], $validated['comments'] ?? null);
            return redirect()->route('booking.confirmation', ['business' => $business, 'appointment' => $appointment->hash]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['time' => $e->getMessage()]);
        }
    }

    public function confirmation(Business $business, string $appointment)
    {
        $appointment = Appointment::where('hash', $appointment)
            ->with(['service', 'contact', 'business'])
            ->firstOrFail();

        return Inertia::render('Booking/Confirmation', [
            'appointment' => $appointment,
            'business' => $business,
        ]);
    }
}
