<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Events\AppointmentWasCanceled;
use App\Events\AppointmentWasConfirmed;
use App\Http\Controllers\Controller;
use App\Http\Requests\AlterAppointmentRequest;
use Illuminate\Http\JsonResponse;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;

class BookingController extends Controller
{
    public function __construct(
        private readonly Concierge $concierge
    ) {}

    public function postAction(AlterAppointmentRequest $request): JsonResponse
    {
        logger()->info(__METHOD__);

        $validated = $request->validated();

        $issuer = auth()->user();
        $business = Business::findOrFail($validated['business']);
        $appointment = Appointment::with(['contact', 'service', 'business'])
            ->findOrFail($validated['appointment']);
        $action = $validated['action'];
        $widgetType = $validated['widget'];

        logger()->info(sprintf(
            'postAction.request:[issuer:%s, action:%s, business:%s, appointment:%s]',
            $issuer->email,
            $action,
            $business->id,
            $appointment->id
        ));

        $this->concierge->business($business);

        $appointmentManager = $this->concierge->booking()->appointment($appointment->hash);

        switch ($action) {
            case 'cancel':
                $appointment = $appointmentManager->cancel();
                event(new AppointmentWasCanceled($issuer, $appointment));
                break;
            case 'confirm':
                $appointment = $appointmentManager->confirm();
                event(new AppointmentWasConfirmed($issuer, $appointment));
                break;
            case 'serve':
                $appointment = $appointmentManager->serve();
                break;
        }

        $appointment->load(['contact', 'service', 'business']);

        $contents = [
            'appointment' => $appointment,
            'user'        => auth()->user(),
        ];

        $viewKey = "widgets.appointment.{$widgetType}._body";
        $html = view($viewKey, $contents)->render();
        $code = 'OK';

        logger()->info("postAction.response:[appointment:{$appointment->toJson()}]");

        return response()->json(compact('code', 'html'));
    }
}
