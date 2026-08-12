<?php

namespace App\Listeners;

use App\Events\AppointmentWasCanceled;
use App\TG\TransMail;

class SendAppointmentCancellationNotification
{
    private $transmail;

    public function __construct(TransMail $transmail)
    {
        $this->transmail = $transmail;
    }

    /**
     * Handle the event.
     *
     * @param AppointmentWasCanceled $event
     *
     * @return void
     */
    public function handle(AppointmentWasCanceled $event)
    {
        logger()->info(__METHOD__);

        $code = $event->appointment->code;
        $date = $event->appointment->start_at->toDateString();
        $businessName = $event->appointment->business->name;

        logger()->info('Appointment cancellation notification', [
            'category'     => 'appointment.cancel',
            'user_id'      => $event->user->id,
            'business_id'  => $event->appointment->business->id,
            'businessName' => $businessName,
            'code'         => $code,
            'date'         => $date,
        ]);

        if ($event->appointment->business->pref('disable_outbound_mailing')) {
            return;
        }

        /////////////////
        // Send emails //
        /////////////////

        // Mail to User
        $params = [
            'user'         => $event->user,
            'appointment'  => $event->appointment,
            'businessName' => $businessName,
            'userName'     => $event->appointment->contact->firstname,
        ];
        $header = [
            'name'  => $event->appointment->contact->firstname,
            'email' => $event->appointment->contact->email,
        ];
        $this->transmail->locale($event->appointment->business->locale)
                        ->timezone($event->user->pref('timezone'))
                        ->template('user.appointment-cancellation.notification')
                        ->subject('user.appointment-cancellation.subject', compact('businessName'))
                        ->send($header, $params);
    }
}
