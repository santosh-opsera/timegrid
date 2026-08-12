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
        $business->load('services');

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

        $service = $business->services()->findOrFail($validated['service_id']);

        $user = $request->user();

        $contact = Contact::query()
            ->where('email', $validated['email'])
            ->where(function ($query) use ($user) {
                $query->whereNull('user_id');
                if ($user) {
                    $query->orWhere('user_id', $user->id);
                }
            })
            ->first();

        if (! $contact) {
            $contact = Contact::create([
                'firstname' => $validated['firstname'],
                'lastname' => $validated['lastname'] ?? '',
                'email' => $validated['email'],
                'mobile' => $validated['phone'] ?? null,
                'gender' => 'F',
                'user_id' => $user?->id,
            ]);
        } elseif (! $contact->user_id && $user) {
            $contact->update(['user_id' => $user->id]);
        }

        if (! $business->contacts()->where('contacts.id', $contact->id)->exists()) {
            $business->contacts()->attach($contact->id);
        }

        try {
            $appointment = $bookingService->book(
                $business,
                $service,
                $contact,
                $validated['date'],
                $validated['time'],
                $validated['comments'] ?? null,
                $user?->id,
            );

            return redirect()->route('booking.confirmation', [
                'business' => $business,
                'appointment' => $appointment->hash,
            ]);
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
