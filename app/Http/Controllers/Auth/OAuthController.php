<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class OAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'facebook', 'github'];

    public function redirectToProvider(string $provider): SymfonyRedirectResponse
    {
        $this->validateProvider($provider);

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(string $provider, Request $request): RedirectResponse
    {
        $this->validateProvider($provider);

        if (!$request->has('code')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'OAuth authorization was cancelled.']);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            logger()->warning('OAuth callback failed', ['provider' => $provider, 'error' => $e->getMessage()]);
            return redirect()->route('login')
                ->withErrors(['email' => 'Unable to authenticate with ' . ucfirst($provider) . '.']);
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'username' => Str::slug($socialUser->getNickname() ?? $socialUser->getName() ?? Str::random(8)),
                'password' => Hash::make(Str::random(32)),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, true);

        return redirect()->intended('/home');
    }

    private function validateProvider(string $provider): void
    {
        validator(['provider' => $provider], [
            'provider' => ['required', Rule::in(self::ALLOWED_PROVIDERS)],
        ])->validate();
    }
}
