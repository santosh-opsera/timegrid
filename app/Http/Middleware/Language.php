<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Response;

class Language
{
    public function __construct(
        private readonly Agent $agent,
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionAppLocale = session()->get('applocale');

        if ($sessionAppLocale === null) {
            $sessionAppLocale = $this->getAgentLangOrFallback(config('app.fallback_locale'));
        }

        if (isAcceptedLocale($sessionAppLocale)) {
            setGlobalLocale($sessionAppLocale);
        }

        return $next($request);
    }

    /**
     * Resolve the preferred locale from the user agent or fall back.
     */
    protected function getAgentLangOrFallback(string $fallbackLocale): string
    {
        $agentLanguages = $this->agent->languages();
        $configLanguages = config('languages', []);

        if ($agentPreferredLocale = $this->searchAgent($agentLanguages, $configLanguages)) {
            return $agentPreferredLocale;
        }

        return $fallbackLocale;
    }

    /**
     * Search agent languages against configured application locales.
     *
     * @param  list<string>  $agentPreferredLocale
     * @param  array<string, string>  $appAcceptedLocales
     */
    protected function searchAgent(array $agentPreferredLocale, array $appAcceptedLocales): ?string
    {
        $availableLangs = $this->normalizeArrayKeys($appAcceptedLocales);
        $agentPreferredLocale = $this->normalizeArrayValues($agentPreferredLocale);

        foreach ($agentPreferredLocale as $agentLang) {
            if ($matchedLocale = $this->compareAgentLang($availableLangs, $agentLang)) {
                return $matchedLocale;
            }
        }

        return null;
    }

    /**
     * Match an agent language token against available locales.
     *
     * @param  array<string, string>  $availableLangs
     */
    protected function compareAgentLang(array $availableLangs, string $agentLang): string|false
    {
        foreach ($availableLangs as $availableKey => $availableLang) {
            if (stripos($availableLang, $agentLang) !== false) {
                return $availableKey;
            }
        }

        return false;
    }

    /**
     * Copy locale keys as lowercase values.
     *
     * @param  array<string, string>  $array
     * @return array<string, string>
     */
    protected function normalizeArrayKeys(array $array): array
    {
        array_walk($array, function (&$value, $key): void {
            $value = strtolower((string) $key);
        });

        return $array;
    }

    /**
     * Normalize locale values to lowercase underscored strings.
     *
     * @param  list<string>  $array
     * @return list<string>
     */
    protected function normalizeArrayValues(array $array): array
    {
        array_walk($array, function (&$value): void {
            $value = str_replace('-', '_', strtolower((string) $value));
        });

        return $array;
    }
}
