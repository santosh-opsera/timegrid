<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Fenos\Notifynder\Facades\Notifynder;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Business;

class BusinessController extends Controller
{
    public function __construct(
        private readonly Concierge $concierge
    ) {}

    public function getHome(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf("businessId:%s businessSlug:'%s'", $business->id, $business->slug));

        $business->load(['services', 'category']);

        $businessName = $business->name;
        Notifynder::category('user.visitedShowroom')
            ->from('App\Models\User', auth()->id())
            ->to('Timegridio\Concierge\Models\Business', $business->id)
            ->url('http://localhost')
            ->extra(compact('businessName'))
            ->send();

        $available = $this->concierge->business($business)->isBookable('today', 30);

        $appointment = $business->bookings()
            ->with(['contact', 'service'])
            ->forContacts(auth()->user()->contacts)
            ->active()
            ->first();

        return Inertia::render('Booking/Show', [
            'business'    => $business,
            'available'   => $available,
            'appointment' => $appointment,
        ]);
    }

    public function getList(): Response
    {
        logger()->info(__METHOD__);

        $businesses = Business::with('category')
            ->where('listed', true)
            ->get();

        return Inertia::render('Directory', [
            'businesses' => $businesses,
        ]);
    }

    public function getSubscriptions(): Response
    {
        logger()->info(__METHOD__);

        $contacts = auth()->user()->contacts()->with('businesses')->get();

        return Inertia::render('Subscriptions/Index', [
            'contacts' => $contacts,
        ]);
    }
}
