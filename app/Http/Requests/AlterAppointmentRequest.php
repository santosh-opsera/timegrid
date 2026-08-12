<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Timegridio\Concierge\Models\Appointment;

class AlterAppointmentRequest extends Request
{
    public function authorize(): bool
    {
        $appointmentId = $this->integer('appointment');
        $businessId = $this->integer('business');

        $appointment = Appointment::with('issuer')->find($appointmentId);

        if ($appointment === null || auth()->user() === null) {
            return false;
        }

        $authorize = auth()->user()->isOwnerOf($businessId)
            || $appointment->issuer->id === auth()->id();

        logger()->info("Authorize:{$authorize}");

        return $authorize;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'business'    => ['required', 'integer', 'exists:businesses,id'],
            'appointment' => ['required', 'integer', 'exists:appointments,id'],
            'action'      => ['required', Rule::in(['confirm', 'cancel', 'serve'])],
            'widget'      => ['required', Rule::in(['row', 'panel'])],
        ];
    }
}
