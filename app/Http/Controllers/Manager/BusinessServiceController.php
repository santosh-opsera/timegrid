<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Service;

class BusinessServiceController extends Controller
{
    public function index(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageServices', $business);

        $business->load(['services.type', 'servicetypes']);

        return Inertia::render('Business/Services/Index', [
            'business' => $business,
        ]);
    }

    public function create(Business $business): Response|RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        if ($business->services()->count() > plan('limits.services', $business->plan)) {
            session()->flash('warning', trans('app.saas.plan_limit_reached'));

            return redirect()->back();
        }

        $this->authorize('manageServices', $business);

        $types = $business->servicetypes()->pluck('name', 'id');

        $service = new Service([
            'duration' => $business->pref('service_default_duration'),
        ]);

        return Inertia::render('Business/Services/Create', [
            'business' => $business,
            'service'  => $service,
            'types'    => $types,
        ]);
    }

    public function store(Business $business, StoreServiceRequest $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageServices', $business);

        $validated = $request->validated();

        $service = Service::firstOrNew($validated);
        $service->business()->associate($business->id);

        if (! empty($validated['type_id'])) {
            $service->type()->associate($validated['type_id']);
        }

        $service->save();

        logger()->info("Stored serviceId:{$service->id}");

        session()->flash('success', trans('manager.service.msg.store.success'));

        return redirect()->route('manager.business.service.show', [$business, $service]);
    }

    public function show(Business $business, Service $service): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s serviceId:%s', $business->id, $service->id));

        $this->authorize('manageServices', $business);

        $service->load(['type', 'business']);

        return Inertia::render('Business/Services/Show', [
            'service' => $service,
        ]);
    }

    public function edit(Business $business, Service $service): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s serviceId:%s', $business->id, $service->id));

        $this->authorize('manageServices', $business);

        $types = $business->servicetypes()->pluck('name', 'id');

        return Inertia::render('Business/Services/Edit', [
            'service' => $service,
            'types'   => $types,
        ]);
    }

    public function update(Business $business, Service $service, Request $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s serviceId:%s', $business->id, $service->id));

        $this->authorize('manageServices', $business);

        $validated = $request->validate([
            'name'           => ['required', 'string', 'min:2', 'max:255'],
            'color'          => ['nullable', 'string', 'max:20'],
            'duration'       => ['required', 'integer', 'min:1', 'max:1440'],
            'description'    => ['nullable', 'string', 'max:2000'],
            'prerequisites'  => ['nullable', 'string', 'max:2000'],
            'type_id'        => ['nullable', 'integer', 'exists:service_types,id'],
        ]);

        $service->update(collect($validated)->only([
            'name',
            'color',
            'duration',
            'description',
            'prerequisites',
        ])->all());

        if (! empty($validated['type_id'])) {
            $service->type()->associate($validated['type_id']);
            $service->save();
        }

        session()->flash('success', trans('manager.business.service.msg.update.success'));

        return redirect()->route('manager.business.service.show', [$business, $service]);
    }

    public function destroy(Business $business, Service $service): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s serviceId:%s', $business->id, $service->id));

        $this->authorize('manageServices', $business);

        $service->forceDelete();

        session()->flash('success', trans('manager.services.msg.destroy.success'));

        return redirect()->route('manager.business.service.index', $business);
    }
}
