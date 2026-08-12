<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Humanresource;

class HumanresourceController extends Controller
{
    public function index(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageHumanresources', $business);

        $humanresources = $business->humanresources()->get();

        return Inertia::render('Business/Staff/Index', [
            'business'       => $business,
            'humanresources' => $humanresources,
        ]);
    }

    public function create(Business $business): Response|RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        if ($business->humanresources()->count() > plan('limits.specialists', $business->plan)) {
            session()->flash('warning', trans('app.saas.plan_limit_reached'));

            return redirect()->back();
        }

        $this->authorize('manageHumanresources', $business);

        $humanresource = new Humanresource();

        return Inertia::render('Business/Staff/Create', [
            'business'      => $business,
            'humanresource' => $humanresource,
        ]);
    }

    public function store(Business $business, Request $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageHumanresources', $business);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'min:2', 'max:255'],
            'capacity'       => ['required', 'integer', 'min:1'],
            'calendar_link'  => ['nullable', 'string', 'max:500'],
        ]);

        $humanresource = new Humanresource($validated);
        $humanresource->business()->associate($business->id);
        $humanresource->save();

        session()->flash('success', trans('manager.humanresources.msg.store.success'));

        return redirect()->route('manager.business.humanresource.show', [$business, $humanresource]);
    }

    public function show(Business $business, Humanresource $humanresource): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s humanresourceId:%s', $business->id, $humanresource->id));

        $this->authorize('manageHumanresources', $business);

        return Inertia::render('Business/Staff/Show', [
            'business'      => $business,
            'humanresource' => $humanresource,
        ]);
    }

    public function edit(Business $business, Humanresource $humanresource): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s humanresourceId:%s', $business->id, $humanresource->id));

        $this->authorize('manageHumanresources', $business);

        return Inertia::render('Business/Staff/Edit', [
            'business'      => $business,
            'humanresource' => $humanresource,
        ]);
    }

    public function update(Business $business, Humanresource $humanresource, Request $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s humanresourceId:%s', $business->id, $humanresource->id));

        $this->authorize('manageHumanresources', $business);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'min:2', 'max:255'],
            'capacity'       => ['required', 'integer', 'min:1'],
            'calendar_link'  => ['nullable', 'string', 'max:500'],
        ]);

        $humanresource->fill($validated);
        $humanresource->save();

        session()->flash('success', trans('manager.humanresources.msg.update.success'));

        return redirect()->route('manager.business.humanresource.show', [$business, $humanresource]);
    }

    public function destroy(Business $business, Humanresource $humanresource): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s humanresourceId:%s', $business->id, $humanresource->id));

        $this->authorize('manageHumanresources', $business);

        $humanresource->delete();

        session()->flash('success', trans('manager.humanresources.msg.destroy.success'));

        return redirect()->route('manager.business.humanresource.index', $business);
    }
}
