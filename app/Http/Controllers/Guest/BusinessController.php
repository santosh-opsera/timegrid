<?php

declare(strict_types=1);

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Domain;

class BusinessController extends Controller
{
    public function getHome(string $slug): Response|RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('slug:%s', $slug));

        if ($domain = Domain::with('businesses')->where('slug', $slug)->first()) {
            return $this->getDomain($domain);
        }

        if ($business = Business::with(['services', 'category'])->where('slug', $slug)->first()) {
            session()->put('guest.last-intended-business-home', $slug);

            return Inertia::render('Booking/Show', [
                'business' => $business,
            ]);
        }

        session()->forget('guest.last-intended-business-home');

        $baseurl = url()->to('/'.$slug);

        session()->flash('success', trans('app.msg.slug_is_available', compact('baseurl')));

        return redirect()->to('/login');
    }

    public function getDomain(Domain $domain): Response|RedirectResponse
    {
        logger()->info(__METHOD__);

        $businesses = $domain->businesses()->with('category')->get();

        if ($businesses->count() === 1) {
            return redirect(route('guest.business.home', $businesses->first()));
        }

        return Inertia::render('Directory', [
            'businesses' => $businesses,
        ]);
    }
}
