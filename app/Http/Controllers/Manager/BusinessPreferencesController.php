<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Fenos\Notifynder\Facades\Notifynder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;

class BusinessPreferencesController extends Controller
{
    public function getPreferences(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('managePreferences', $business);

        $parameters = config()->get('preferences.Timegridio\Concierge\Models\Business');
        $preferences = $business->preferences;

        return Inertia::render('Business/Preferences/Edit', [
            'business'    => $business,
            'preferences' => $preferences,
            'parameters'  => $parameters,
        ]);
    }

    public function postPreferences(Business $business, Request $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('managePreferences', $business);

        $parameters = config()->get('preferences.Timegridio\Concierge\Models\Business');
        $parameterKeys = array_flip(array_keys($parameters));
        $validated = array_intersect_key($request->validate(
            collect($parameters)->mapWithKeys(fn (array $config, string $key): array => [
                $key => $this->preferenceRule($config),
            ])->all()
        ), $parameterKeys);

        $this->setBusinessPreferences($business, $validated);

        $businessName = $business->name;
        Notifynder::category('user.updatedBusinessPreferences')
            ->from('App\Models\User', auth()->id())
            ->to('Timegridio\Concierge\Models\Business', $business->id)
            ->url('http://localhost')
            ->extra(compact('businessName'))
            ->send();

        flash()->success(trans('manager.businesses.msg.preferences.success'));

        return redirect()->route('manager.business.show', $business);
    }

    /**
     * @param  array<string, mixed>  $preferences
     */
    protected function setBusinessPreferences(Business $business, array $preferences): void
    {
        $parameters = config()->get('preferences.Timegridio\Concierge\Models\Business');

        foreach ($preferences as $key => $value) {
            logger()->info(sprintf(
                "set preference: businessId:%s key:%s='%s' type:%s",
                $business->id,
                $key,
                $value,
                $parameters[$key]['type']
            ));

            $business->pref($key, $value, $parameters[$key]['type']);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<string>
     */
    private function preferenceRule(array $config): array
    {
        return match ($config['type'] ?? 'string') {
            'boolean' => ['nullable', 'boolean'],
            'integer' => ['nullable', 'integer'],
            default   => ['nullable', 'string', 'max:255'],
        };
    }
}
