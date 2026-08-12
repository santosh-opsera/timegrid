<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\ServiceType;

class ServiceTypeController extends Controller
{
    public function edit(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageServices', $business);

        $servicetypes = $business->servicetypes()->get()->all();

        return Inertia::render('Business/Services/Types/Edit', [
            'business'     => $business,
            'servicetypes' => $servicetypes,
        ]);
    }

    public function update(Business $business, Request $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageServices', $business);

        $validated = $request->validate([
            'servicetypes' => ['required', 'string'],
        ]);

        $servicetypeSheet = $validated['servicetypes'];

        $regex = '/(?P<name>[a-zA-Z\d\-\ ]+)\:(?P<description>[a-zA-Z\d\ ]+)/im';

        preg_match_all($regex, $servicetypeSheet, $matches, PREG_SET_ORDER);

        $publishing = collect($matches)->map(
            function (array $item): array {
                $data = Arr::only($item, ['name', 'description']);
                $data['slug'] = Str::slug($data['name']);

                return $data;
            }
        );

        foreach ($business->servicetypes as $servicetype) {
            if (! $this->isPublished($servicetype, $publishing)) {
                $servicetype->delete();
            }
        }

        foreach ($publishing as $servicetypeData) {
            $servicetype = ServiceType::firstOrNew($servicetypeData);

            $business->servicetypes()->save($servicetype);
        }

        flash()->success(trans('servicetype.msg.update.success'));

        return redirect()->route('manager.business.service.index', [$business]);
    }

    protected function isPublished(ServiceType $servicetype, Collection $publishing): bool
    {
        foreach ($publishing as $key => $item) {
            if ($item['name'] == $servicetype->name) {
                $publishing->forget($key);

                return true;
            }
        }

        return false;
    }
}
