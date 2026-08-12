<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'min:4', 'max:255'],
            'description'      => ['required', 'string', 'min:10'],
            'timezone'         => ['required', 'timezone:all'],
            'strategy'         => ['required', 'string', Rule::in(['timeslot', 'schedule'])],
            'category'         => ['required', 'integer', 'exists:categories,id'],
            'postal_address'   => ['nullable', 'string', 'max:500'],
            'phone'            => ['nullable', 'string', 'max:50'],
            'social_facebook'  => ['nullable', 'string', 'max:255'],
            'plan'             => ['nullable', 'string', 'max:50'],
            'country_code'     => ['nullable', 'string', 'size:2'],
            'locale'           => ['nullable', 'string', 'max:10'],
        ];
    }
}
