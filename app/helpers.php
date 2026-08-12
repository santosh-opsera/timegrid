<?php

use Carbon\Carbon;

if (! function_exists('setGlobalLocale')) {
    /**
     * Set locale among all localizable contexts.
     */
    function setGlobalLocale(string $posixLocale): void
    {
        app()->setLocale($posixLocale);
        setlocale(LC_TIME, $posixLocale);
        Carbon::setLocale(locale_get_primary_language($posixLocale));
    }
}

if (! function_exists('isAcceptedLocale')) {
    /**
     * Determine if a POSIX language string is accepted by app config.
     */
    function isAcceptedLocale(string $posixLocale): bool
    {
        return array_key_exists($posixLocale, config('languages', []));
    }
}

if (! function_exists('trans_duration')) {
    /**
     * Localize a human-friendly duration string.
     */
    function trans_duration(string $string): string
    {
        $translations = [
            'hour' => trans_choice('datetime.duration.hours', 1),
            'hours' => trans_choice('datetime.duration.hours', 2),
            'minute' => trans_choice('datetime.duration.minutes', 1),
            'minutes' => trans_choice('datetime.duration.minutes', 2),
            'second' => trans_choice('datetime.duration.seconds', 1),
            'seconds' => trans_choice('datetime.duration.seconds', 2),
        ];

        return str_replace(array_keys($translations), array_values($translations), $string);
    }
}

if (! function_exists('str_link')) {
    /**
     * Generate a link string with fallback.
     */
    function str_link(string $route, ?string $caption, string $fallbackCaption = '#N/A'): string
    {
        $caption = trim((string) $caption);

        return '<a href="'.e($route).'">'.e($caption !== '' ? $caption : $fallbackCaption).'</a>';
    }
}

if (! function_exists('format_business_notifications')) {
    /**
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Notifications\DatabaseNotification>|\Illuminate\Support\Collection<int, mixed>  $notifications
     * @return list<array<string, mixed>>
     */
    function format_business_notifications($notifications): array
    {
        return $notifications->map(function ($notification) {
            $data = is_array($notification->data ?? null)
                ? $notification->data
                : (array) ($notification->data ?? []);

            $category = (string) ($data['category'] ?? '');
            $fromName = (string) ($data['from']['name'] ?? 'Someone');
            $extra = (array) ($data['extra'] ?? []);

            return [
                'id'         => $notification->id,
                'category'   => $category,
                'body'       => trans("notifications.{$category}", ['user' => $fromName] + $extra),
                'url'        => $data['url'] ?? null,
                'created_at' => $notification->created_at?->toIso8601String(),
                'read_at'    => $notification->read_at?->toIso8601String(),
            ];
        })->values()->all();
    }
}

if (! function_exists('docs_url')) {
    /**
     * Generate a link to user manual documentation.
     */
    function docs_url(?string $locale = 'en'): string
    {
        $locale = $locale ?: 'en';

        return (string) config("root.docs_url.{$locale}");
    }
}
