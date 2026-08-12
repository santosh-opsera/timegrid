<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Timegridio\Concierge\Models\Business;

class StoreVacancyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Business|null $business */
        $business = $this->route('business');

        return $business !== null && $this->user()?->can('manageVacancies', $business) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->routeIs('manager.business.vacancy.storeBatch')) {
            return [
                'vacancies'  => ['required', 'string'],
                'unpublish'  => ['nullable', 'boolean'],
                'remember'   => ['nullable', 'boolean'],
            ];
        }

        if ($this->routeIs('manager.business.vacancy.update')) {
            return [
                'serviceId'  => ['required', 'integer', 'exists:services,id'],
                'weekdays'   => ['required', 'array'],
                'weekdays.*' => ['nullable', Rule::in(['on', '1', 1, true])],
            ];
        }

        return [
            'vacancy'      => ['required', 'array'],
            'vacancy.*'    => ['array'],
            'vacancy.*.*'  => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }
}
