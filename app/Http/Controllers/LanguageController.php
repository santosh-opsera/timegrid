<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    private const SUPPORTED_LOCALES = ['en', 'es', 'fr', 'it'];

    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            abort(400, 'Unsupported locale');
        }

        $request->session()->put('locale', $locale);

        return redirect()->back();
    }
}
