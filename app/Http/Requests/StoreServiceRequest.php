<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Timegridio\Concierge\Models\Business;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Business|null $business */
        $business = $this->route('business');

        return $business !== null && $this->user()?->can('manageServices', $business) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'min:2', 'max:255'],
            'duration'       => ['required', 'integer', 'min:1', 'max:1440'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'prerequisites'  => ['nullable', 'string', 'max:2000'],
            'color'          => ['nullable', 'string', 'max:20'],
            'type_id'        => ['nullable', 'integer', 'exists:service_types,id'],
        ];
    }
}
