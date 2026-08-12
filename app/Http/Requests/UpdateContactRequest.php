<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

class UpdateContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Contact|null $contact */
        $contact = $this->route('contact');

        if ($contact !== null && $this->user()?->can('manage', $contact) === true) {
            return true;
        }

        /** @var Business|null $business */
        $business = $this->route('business');

        return $business !== null && $this->user()?->can('manageContacts', $business) === true;
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
