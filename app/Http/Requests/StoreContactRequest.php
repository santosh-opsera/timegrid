<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Timegridio\Concierge\Models\Business;

class StoreContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Business|null $business */
        $business = $this->route('business');

        if ($business === null) {
            return auth()->check();
        }

        return $this->user()?->can('manageContacts', $business) === true
            || auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'firstname'       => ['required', 'string', 'min:3', 'max:255'],
            'lastname'        => ['required', 'string', 'min:2', 'max:255'],
            'gender'          => ['required', 'string', Rule::in(['M', 'F'])],
            'email'           => ['nullable', 'email', 'max:255'],
            'birthdate'       => ['nullable', 'date'],
            'nin'             => ['nullable', 'string', 'max:50'],
            'mobile'          => ['nullable', 'string', 'max:50'],
            'mobile_country'  => ['nullable', 'string', 'max:5'],
            'postal_address'  => ['nullable', 'string', 'max:500'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ];
    }
}
