<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserPreferencesController extends Controller
{
    public function getPreferences(): Response
    {
        logger()->info(__METHOD__);

        $parameters = config()->get('preferences.App\Models\User');
        $preferences = auth()->user()->preferences;

        return Inertia::render('Profile/Edit', [
            'preferences' => $preferences,
            'parameters'  => $parameters,
        ]);
    }

    public function postPreferences(Request $request): RedirectResponse
    {
        logger()->info(__METHOD__);

        $parameters = config()->get('preferences.App\Models\User');
        $parameterKeys = array_flip(array_keys($parameters));
        $validated = array_intersect_key($request->validate(
            collect($parameters)->mapWithKeys(fn (array $config, string $key): array => [
                $key => $this->preferenceRule($config),
            ])->all()
        ), $parameterKeys);

        $this->setUserPreferences($validated);

        flash()->success(trans('user.msg.preferences.success'));

        return redirect()->back();
    }

    /**
     * @param  array<string, mixed>  $preferences
     */
    protected function setUserPreferences(array $preferences): void
    {
        $parameters = config()->get('preferences.App\Models\User');

        foreach ($preferences as $key => $value) {
            logger()->info(sprintf(
                "set preference: UserId:%s key:%s='%s' type:%s",
                auth()->user()->id,
                $key,
                $value,
                $parameters[$key]['type']
            ));

            auth()->user()->pref($key, $value, $parameters[$key]['type']);
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
