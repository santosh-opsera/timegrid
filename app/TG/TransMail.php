<?php

declare(strict_types=1);

namespace App\TG;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;

class TransMail
{
    protected string $locale = 'en_US';
    protected string $localeSwitchFunction = 'setGlobalLocale';
    protected string $revertLocale = 'en_US';
    protected ?string $timezone = null;
    protected ?string $revertTimezone = null;
    protected string $subjectKey = '';
    protected array $subjectParams = [];
    protected string $viewBase = 'emails';
    protected string $viewPath = '';
    protected string $subject = '';
    protected bool $success = false;

    public function useFunction(string $functionName): static
    {
        $this->localeSwitchFunction = $functionName;

        return $this;
    }

    public function locale(?string $posixLocale = null): static
    {
        $this->revertLocale = app()->getLocale();

        if ($posixLocale === null) {
            $posixLocale = $this->revertLocale;
        }

        $this->locale = $posixLocale;

        return $this;
    }

    public function timezone(?string $timezone): static
    {
        $this->revertTimezone = session()->get('timezone');
        $this->timezone = $timezone;

        return $this;
    }

    public function switchTimezone(?string $timezone): static
    {
        if ($timezone !== null && $timezone !== '') {
            $this->revertTimezone = session()->get('timezone');
            session()->put('timezone', $timezone);
        }

        return $this;
    }

    public function template(string $template): static
    {
        $this->viewPath = $template;

        return $this;
    }

    public function subject(string $key, array $params = []): static
    {
        $this->subjectKey = $key;
        $this->subjectParams = $params;

        return $this;
    }

    public function send(array $header, array $params): bool
    {
        $this->switchLocale($this->locale);
        $this->switchTimezone($this->timezone);

        $email = Arr::get($header, 'email');
        $name = Arr::get($header, 'name');
        $viewKey = $this->getViewKey();
        $subject = $this->getSubject();

        Mail::send($viewKey, $params, function ($message) use ($email, $name, $subject) {
            $message->to($email, $name)->subject($subject);
        });

        $this->switchLocale($this->revertLocale);
        $this->switchTimezone($this->revertTimezone);

        $this->success = count(Mail::failures()) === 0;

        return $this->success();
    }

    public function success(): bool
    {
        return $this->success;
    }

    protected function switchLocale(string $posixLocale): static
    {
        if (function_exists($this->localeSwitchFunction)) {
            call_user_func($this->localeSwitchFunction, $posixLocale);
        }

        return $this;
    }

    protected function getViewKey(): string
    {
        $key = $this->viewBase . '.' . $this->viewPath;

        if (!view()->exists($key)) {
            throw new \RuntimeException('Email view does not exist: ' . $key);
        }

        return $key;
    }

    protected function getSubject(): string
    {
        return $this->subject = trans('emails.' . $this->subjectKey, $this->subjectParams);
    }
}
