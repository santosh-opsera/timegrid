<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $locale = App::getLocale();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'can' => $user ? [
                    'manage_businesses' => $user->role !== \App\Enums\UserRole::Customer,
                ] : [],
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => $locale,
            'available_locales' => [
                'en' => 'English',
                'es' => 'Español',
                'fr' => 'Français',
                'it' => 'Italiano',
            ],
            'translations' => fn () => $this->loadTranslations($locale),
        ];
    }

    private function loadTranslations(string $locale): array
    {
        $path = lang_path("{$locale}.json");

        if (!file_exists($path)) {
            $path = lang_path('en.json');
        }

        return json_decode(file_get_contents($path), true) ?: [];
    }
}
