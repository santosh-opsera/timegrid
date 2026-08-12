<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'businessId'  => ['required', 'integer', 'exists:businesses,id'],
            'service_id'  => ['required', 'integer', 'exists:services,id'],
            '_date'       => ['required', 'date', 'after_or_equal:today'],
            '_time'       => ['required', 'date_format:H:i'],
            '_timezone'   => ['nullable', 'timezone:all'],
            'comments'    => ['nullable', 'string', 'max:1000'],
            'contact_id'  => ['nullable', 'integer', 'exists:contacts,id'],
            'email'       => ['nullable', 'email', 'max:255'],
        ];
    }
}
