<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $businesses = $user->ownedBusinesses()->get();
        foreach ($businesses as $business) {
            $business->loadCount(['services', 'appointments']);
            $business->contacts_count = $business->contacts()->count();
        }

        $isOwner = $businesses->isNotEmpty();
        $canceled = AppointmentStatus::Canceled->value;

        if ($isOwner) {
            $businessIds = $businesses->pluck('id');

            $upcomingAppointments = Appointment::whereIn('business_id', $businessIds)
                ->where('start_at', '>=', now())
                ->whereNot('status', $canceled)
                ->with(['service', 'business', 'contact'])
                ->orderBy('start_at')
                ->limit(20)
                ->get();
        } else {
            $upcomingAppointments = Appointment::whereHas('contact', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
                ->where('start_at', '>=', now())
                ->whereNot('status', $canceled)
                ->with(['service', 'business'])
                ->orderBy('start_at')
                ->limit(10)
                ->get();
        }

        return Inertia::render('Dashboard', [
            'businesses' => $businesses,
            'upcomingAppointments' => $upcomingAppointments,
            'isOwner' => $isOwner,
        ]);
    }
}
