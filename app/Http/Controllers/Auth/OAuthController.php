<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\AuthenticateUser;
use App\AuthenticateUserListener;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class OAuthController extends Controller implements AuthenticateUserListener
{
    use RedirectsUsers, ThrottlesLogins;

    /** @var list<string> */
    private const ALLOWED_PROVIDERS = ['google', 'facebook', 'github'];

    public function __construct()
    {
        $this->redirectPath = route('home');
    }

    public function redirectToProvider(string $provider): SymfonyRedirectResponse
    {
        $this->validateProvider($provider);

        logger()->info(__METHOD__);
        logger()->info(sprintf('provider:%s', $provider));

        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback(
        string $provider,
        AuthenticateUser $authenticateUser,
        Request $request
    ): RedirectResponse|SymfonyRedirectResponse {
        $this->validateProvider($provider);

        $hasCode = $request->has('code');

        return $authenticateUser->execute($provider, $hasCode, $this);
    }

    public function userHasLoggedIn(mixed $user): RedirectResponse
    {
        return redirect()->intended($this->redirectPath());
    }

    private function validateProvider(string $provider): void
    {
        validator(['provider' => $provider], [
            'provider' => ['required', Rule::in(self::ALLOWED_PROVIDERS)],
        ])->validate();
    }
}
