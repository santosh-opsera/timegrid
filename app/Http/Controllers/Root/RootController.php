<?php

declare(strict_types=1);

namespace App\Http\Controllers\Root;

use App\Http\Controllers\Controller;
use App\Http\Middleware\TrackImpersonation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RootController extends Controller
{
    public function getIndex(): Response
    {
        logger()->info(__METHOD__);
        logger()->warning('[ROOT ACCESS]');

        $users = User::with(['businesses', 'contacts'])->get();

        return Inertia::render('Root/Dashboard', [
            'users' => $users,
        ]);
    }

    public function getSudo(int|string $userId): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->warning('[!] ROOT SUDO initiated', ['user_id' => (int) $userId]);

        $impersonator = auth()->user();
        TrackImpersonation::begin($impersonator, (int) $userId);

        auth()->loginUsingId((int) $userId);

        session()->flash('warning', 'ADVICE: THIS IS FOR AUTHORIZED USE ONLY AND YOUR ACTIONS ARE BEING RECORDERED !!!');

        return redirect()->route('user.directory.list');
    }
}
