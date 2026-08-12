<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\TG\Business\Token as BusinessToken;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Business;

class BusinessAgendaController extends Controller
{
    public function __construct(
        private readonly Concierge $concierge
    ) {}

    public function getIndex(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manage', $business);

        $appointments = $this->concierge->business($business)->getUnarchivedAppointments();

        $user = auth()->user();

        return Inertia::render('Business/Agenda/Index', [
            'business'     => $business,
            'appointments' => $appointments,
            'user'         => $user,
            'strategy'     => $business->strategy,
            'isEmpty'      => count($appointments) === 0,
        ]);
    }

    public function getCalendar(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manage', $business);

        $appointments = $this->concierge->business($business)->getActiveAppointments();

        $jsAppointments = [];

        foreach ($appointments as $appointment) {
            $jsAppointments[] = [
                'title' => $appointment->contact->firstname.' / '.$appointment->service->name,
                'color' => $appointment->service->color,
                'start' => $appointment->start_at->timezone($business->timezone)->toIso8601String(),
                'end'   => $appointment->finish_at->timezone($business->timezone)->toIso8601String(),
            ];
        }

        $slotDuration = count($appointments) > 5 ? '0:15' : '0:30';
        $icalURL = $this->generateICalURL($business);

        return Inertia::render('Business/Agenda/Calendar', [
            'business'     => $business,
            'icalURL'      => $icalURL,
            'calendarData' => [
                'minTime'      => $business->pref('start_at'),
                'maxTime'      => $business->pref('finish_at'),
                'events'       => $jsAppointments,
                'lang'         => $this->getActiveLanguage($business->locale),
                'slotDuration' => $slotDuration,
            ],
        ]);
    }

    protected function getActiveLanguage(string $locale): string
    {
        return session()->get('language', substr($locale, 0, 2));
    }

    protected function generateICalURL(Business $business): string
    {
        $businessToken = new BusinessToken($business);

        return route('business.ical.download', [$business, $businessToken->generate()]);
    }
}
