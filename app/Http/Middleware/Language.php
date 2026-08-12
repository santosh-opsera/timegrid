<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Language
{
    public function handle(Request $request, Closure $next): Response
    {
        $sessionAppLocale = session()->get('applocale');

        if ($sessionAppLocale === null) {
            $sessionAppLocale = $this->getPreferredLocale($request, config('app.fallback_locale', 'en'));
        }

        if (function_exists('isAcceptedLocale') && isAcceptedLocale($sessionAppLocale)) {
            if (function_exists('setGlobalLocale')) {
                setGlobalLocale($sessionAppLocale);
            } else {
                app()->setLocale($sessionAppLocale);
            }
        }

        return $next($request);
    }

    protected function getPreferredLocale(Request $request, string $fallback): string
    {
        $acceptLanguage = $request->header('Accept-Language', '');
        $configLanguages = config('languages', []);

        if (empty($acceptLanguage) || empty($configLanguages)) {
            return $fallback;
        }

        $browserLocales = $this->parseAcceptLanguage($acceptLanguage);
        $availableLocales = array_keys($configLanguages);

        foreach ($browserLocales as $browserLocale) {
            $normalized = str_replace('-', '_', strtolower($browserLocale));
            foreach ($availableLocales as $appLocale) {
                if (stripos(strtolower($appLocale), $normalized) !== false) {
                    return $appLocale;
                }
            }
        }

        return $fallback;
    }

    /**
     * @return list<string>
     */
    protected function parseAcceptLanguage(string $header): array
    {
        $locales = [];

        foreach (explode(',', $header) as $part) {
            $part = trim($part);
            if (str_contains($part, ';')) {
                [$locale] = explode(';', $part);
            } else {
                $locale = $part;
            }
            $locales[] = trim($locale);
        }

        return $locales;
    }
}
