<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isOwner = $user->role !== UserRole::Customer;

        $businesses = $isOwner
            ? $user->ownedBusinesses()->withCount(['services', 'contacts', 'appointments'])->get()
            : collect();

        if ($isOwner && $businesses->isNotEmpty()) {
            $businessIds = $businesses->pluck('id');

            $upcomingAppointments = Appointment::whereIn('business_id', $businessIds)
                ->where('start_at', '>=', now())
                ->whereNot('status', 'canceled')
                ->with(['service', 'business', 'contact'])
                ->orderBy('start_at')
                ->limit(20)
                ->get();
        } else {
            $upcomingAppointments = Appointment::whereHas('contact', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->where('start_at', '>=', now())
                ->whereNot('status', 'canceled')
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
