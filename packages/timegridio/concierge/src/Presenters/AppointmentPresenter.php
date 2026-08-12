<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Presenters;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Timegridio\Concierge\Duration;
use Timegridio\Concierge\Enums\AppointmentStatus;
use Timegridio\Concierge\Models\Appointment;

class AppointmentPresenter extends Presenter
{
    protected ?string $timezone = null;

    public function __construct(Appointment $resource)
    {
        parent::__construct($resource);

        if (function_exists('session') && session()->has('timezone')) {
            $this->setTimezone((string) session()->get('timezone'));
        }
    }

    protected function appointment(): Appointment
    {
        /** @var Appointment $resource */
        $resource = $this->resource;

        return $resource;
    }

    public function setTimezone(string|false $timezone = false): self
    {
        $this->timezone = $timezone !== false ? $timezone : null;

        return $this;
    }

    public function timezone(): string
    {
        if ($this->timezone === null) {
            $this->timezone = $this->appointment()->business->timezone;
        }

        return $this->timezone;
    }

    public function code(): string
    {
        $length = (int) $this->appointment()->business->pref('appointment_code_length');

        return strtoupper(substr($this->appointment()->hash, 0, $length));
    }

    public function date(string $format = 'Y-m-d'): string
    {
        $dateFormat = $this->dateFormat($format);

        return $this->appointment()
            ->start_at
            ->timezone($this->timezone())
            ->format($dateFormat);
    }

    public function time(): string
    {
        return $this->appointment()
            ->start_at
            ->timezone($this->timezone())
            ->format($this->timeFormat());
    }

    /**
     * @return array{at: string}|array{from: string, to: string}
     */
    public function arriveAt(): array
    {
        $timeFormat = $this->timeFormat();

        if (! $this->appointment()->business->pref('appointment_flexible_arrival')) {
            return ['at' => $this->time()];
        }

        return [
            'from' => $this->appointment()->vacancy->start_at->timezone($this->timezone())->format($timeFormat),
            'to' => $this->appointment()->vacancy->finish_at->timezone($this->timezone())->format($timeFormat),
        ];
    }

    public function finishTime(): string
    {
        return $this->appointment()
            ->finish_at
            ->timezone($this->timezone())
            ->format($this->timeFormat());
    }

    public function duration(): string
    {
        $duration = new Duration((int) $this->appointment()->duration() * 60000);

        return $duration->format([
            'template' => '{hours} {minutes} {seconds}',
            '{hours}' => '{hours} hours',
            '{minutes}' => '{minutes} minutes',
            '{seconds}' => '{seconds} seconds',
        ]);
    }

    public function phone(): ?string
    {
        return $this->appointment()->business->phone;
    }

    public function location(): ?string
    {
        return $this->appointment()->business->postal_address;
    }

    public function statusLetter(): string
    {
        return substr((string) trans('appointments.status.'.$this->appointment()->status_label), 0, 1);
    }

    public function status(): string
    {
        return (string) trans('appointments.status.'.$this->appointment()->status_label);
    }

    public function statusToCssClass(): string
    {
        return match ($this->appointment()->status) {
            AppointmentStatus::Annulated => 'danger',
            AppointmentStatus::Confirmed => 'success',
            AppointmentStatus::Reserved => 'warning',
            AppointmentStatus::Served => 'default',
            default => 'default',
        };
    }

    public function panel(): string
    {
        return $this->renderView('widgets.appointment.panel._body');
    }

    public function row(): string
    {
        return $this->renderView('widgets.appointment.row._body');
    }

    protected function renderView(string $view): string
    {
        if (! function_exists('view')) {
            return '';
        }

        /** @var View $rendered */
        $rendered = view($view, [
            'appointment' => $this,
            'user' => Auth::user(),
        ]);

        return $rendered->render();
    }

    protected function timeFormat(): string
    {
        return (string) ($this->appointment()->business->pref('time_format') ?: 'h:i a');
    }

    protected function dateFormat(string $defaultFormat = 'Y-m-d'): string
    {
        return (string) ($this->appointment()->business->pref('date_format') ?: $defaultFormat);
    }
}
