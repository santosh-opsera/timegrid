<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    private const SUPPORTED_LOCALES = ['en', 'es', 'fr', 'it'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if (!$locale || !in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = $this->detectFromBrowser($request);
        }

        App::setLocale($locale);

        return $next($request);
    }

    private function detectFromBrowser(Request $request): string
    {
        $preferred = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);

        return $preferred ?? config('app.locale', 'en');
    }
}
