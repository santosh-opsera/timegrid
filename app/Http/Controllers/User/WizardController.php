<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WizardController extends Controller
{
    public function getWizard(): Response|RedirectResponse
    {
        logger()->info(__METHOD__);

        if ($slug = session()->pull('guest.last-intended-business-home')) {
            logger()->info('Resume Business visit to:'.$slug);

            return redirect()->to('/'.$slug);
        }

        if (auth()->user()->hasBusiness()) {
            logger()->info('User has Business');

            return redirect()->route('manager.business.index');
        }

        if (auth()->user()->hasContacts()) {
            logger()->info('User has Contacts');

            return redirect()->route('user.dashboard');
        }

        return Inertia::render('Dashboard');
    }

    public function getDashboard(): Response
    {
        logger()->info(__METHOD__);

        $appointments = auth()->user()
            ->appointments()
            ->with(['business', 'service', 'contact'])
            ->orderBy('start_at')
            ->unarchived()
            ->get();

        $appointmentsCount = $appointments->count();
        $subscriptionsCount = auth()->user()->contacts()->count();

        return Inertia::render('Dashboard', [
            'appointments'         => $appointments,
            'appointmentsCount'    => $appointmentsCount,
            'subscriptionsCount'   => $subscriptionsCount,
        ]);
    }

    public function getWelcome(): Response
    {
        logger()->info(__METHOD__);

        return Inertia::render('Dashboard');
    }

    public function getPricing(): Response
    {
        logger()->info(__METHOD__);

        return Inertia::render('Manager/Pricing');
    }

    public function getTerms(): Response
    {
        logger()->info(__METHOD__);

        return Inertia::render('Manager/Terms');
    }
}
