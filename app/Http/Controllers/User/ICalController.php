<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\TG\Business\Token as BusinessToken;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Timegridio\Concierge\Models\Business;

class ICalController extends Controller
{
    public function download(Business $business, string $token): Response
    {
        logger()->info(__METHOD__);

        $validToken = (new BusinessToken($business))->generate();

        $validator = Validator::make(compact('token'), [
            'token' => "bail|required|alpha_num|max:32|in:{$validToken}",
        ]);

        if ($validator->fails()) {
            abort(403);
        }

        $vCalendar = new Calendar($business->slug);
        $vCalendar->setPublishedTTL('PT1H');

        $events = $this->buildEvents($business);

        foreach ($events as $event) {
            $vCalendar->addComponent($event);
        }

        $content = $vCalendar->render();

        return response($content)
            ->header('Content-Type', 'text/calendar; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="calendar.ics"');
    }

    /**
     * @return list<Event>
     */
    protected function buildEvents(Business $business): array
    {
        $businessAppointments = $business->bookings()
            ->with(['contact', 'service', 'business'])
            ->active()
            ->get();

        $ownerAppointments = $business->owner()
            ->appointments()
            ->with(['contact', 'service', 'business'])
            ->active()
            ->get();

        $appointments = array_merge($businessAppointments->all(), $ownerAppointments->all());

        $events = [];

        foreach ($appointments as $appointment) {
            $vEvent = new Event();

            $startAt = new \DateTime(
                $appointment->start_at->timezone($business->timezone)->toDateTimeString(),
                new \DateTimeZone($business->timezone)
            );
            $endAt = new \DateTime(
                $appointment->finish_at->timezone($business->timezone)->toDateTimeString(),
                new \DateTimeZone($business->timezone)
            );

            $vEvent->setDtStart($startAt);
            $vEvent->setDtEnd($endAt);
            $vEvent->setStatus($this->mapStatus($appointment->status));
            $vEvent->setUniqueId($appointment->business->slug.':'.$appointment->code.'@timegrid.io');

            $summary = $appointment->contact->firstname.'/'.
                $appointment->service->name.'@'.
                $appointment->business->slug.
                ' ['.$appointment->code.']';

            $vEvent->setSummary($summary);
            $vEvent->setDescription($appointment->comments);
            $vEvent->setUseTimezone(true);

            $events[] = $vEvent;
        }

        return $events;
    }

    protected function mapStatus(string $status): string
    {
        $mapping = [
            'R' => 'TENTATIVE',
            'C' => 'CONFIRMED',
            'A' => 'CANCELLED',
            'S' => 'CONFIRMED',
        ];

        return Arr::get($mapping, $status, 'TENTATIVE');
    }
}
