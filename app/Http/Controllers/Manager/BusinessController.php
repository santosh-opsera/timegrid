<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Exceptions\BusinessAlreadyRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\TG\Business\Dashboard;
use App\TG\BusinessService;
use Carbon\Carbon;
use Fenos\Notifynder\Facades\Notifynder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Category;

class BusinessController extends Controller
{
    /** @var array<string, mixed>|null */
    protected ?array $location = null;

    public function __construct(
        private readonly BusinessService $businessService,
        private readonly Carbon $time
    ) {}

    public function index(): Response|RedirectResponse
    {
        logger()->info(__METHOD__);

        $businesses = auth()->user()->businesses()->with('category')->get();

        if ($businesses->count() === 1) {
            logger()->info('Only one business to show');

            flash()->success(trans('manager.businesses.msg.index.only_one_found'));

            return redirect()->route('manager.business.show', $businesses->first());
        }

        $user = auth()->user();

        return Inertia::render('Business/Index', [
            'businesses' => $businesses,
            'user'       => $user,
        ]);
    }

    public function create(string $plan = 'free'): Response
    {
        logger()->info(__METHOD__);
        logger()->info("plan:$plan");

        $timezone = $this->guessTimezone(null);
        $countryCode = $this->getCountry();
        $locale = app()->getLocale();
        $categories = $this->listCategories();
        $business = new Business();

        return Inertia::render('Business/Create', [
            'business'    => $business,
            'timezone'    => $timezone,
            'categories'  => $categories,
            'plan'        => $plan,
            'countryCode' => $countryCode,
            'locale'      => $locale,
        ]);
    }

    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        logger()->info(__METHOD__);

        $validated = $request->validated();

        try {
            $business = $this->businessService->register(
                auth()->user(),
                $validated,
                (int) $validated['category']
            );

            $this->businessService->setup($business);
        } catch (BusinessAlreadyRegistered $exception) {
            flash()->error(trans('manager.businesses.msg.store.business_already_exists'));

            return redirect()->back()->withInput($validated);
        }

        $businessName = $business->name;
        Notifynder::category('user.registeredBusiness')
            ->from('App\Models\User', auth()->id())
            ->to('Timegridio\Concierge\Models\Business', $business->id)
            ->url('http://localhost')
            ->extra(compact('businessName'))
            ->send();

        flash()->success(trans('manager.businesses.msg.store.success'));

        return redirect()->route('manager.business.service.create', $business);
    }

    public function show(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manage', $business);

        session()->put('selected.business', $business);

        $notifications = Notifynder::entity(Business::class)->getNotRead($business->id, 20);

        Notifynder::entity(Business::class)->readAll($business->id);

        $this->time->timezone($business->timezone);

        $dashboard = new Dashboard($business, $this->time);
        $boxes = $dashboard->getBoxes();
        $time = $this->time->toTimeString();

        $business->load(['category', 'services', 'contacts']);

        return Inertia::render('Business/Show', [
            'business'      => $business,
            'notifications' => $notifications,
            'boxes'         => $boxes,
            'time'          => $time,
        ]);
    }

    public function edit(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('update', $business);

        $timezone = $this->guessTimezone($business->timezone);
        $categories = $this->listCategories();
        $category = $business->category_id;

        logger()->info(sprintf('businessId:%s timezone:%s category:%s', $business->id, $timezone, $category));

        return Inertia::render('Business/Edit', [
            'business'   => $business,
            'category'   => $category,
            'categories' => $categories,
            'timezone'   => $timezone,
        ]);
    }

    public function update(Business $business, UpdateBusinessRequest $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('update', $business);

        $validated = $request->validated();
        $category = (int) $validated['category'];

        $data = collect($validated)->only([
            'name',
            'description',
            'timezone',
            'postal_address',
            'phone',
            'social_facebook',
        ])->all();

        $this->businessService->update($business, $data);
        $this->businessService->setCategory($business, $category);

        flash()->success(trans('manager.businesses.msg.update.success'));

        return redirect()->route('manager.business.show', compact('business'));
    }

    public function destroy(Business $business): RedirectResponse
    {
        logger()->info(__METHOD__);

        $this->authorize('destroy', $business);

        logger()->info(sprintf('Deactivating: businessId:%s', $business->id));

        $this->businessService->deactivate($business);

        flash()->success(trans('manager.businesses.msg.destroy.success'));

        return redirect()->route('manager.business.index');
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    protected function listCategories()
    {
        return Category::pluck('slug', 'id')->transform(
            fn (string $item): string => trans("app.business.category.{$item}")
        );
    }

    protected function guessTimezone(?string $timezone = null): ?string
    {
        if (! empty($timezone)) {
            return $timezone;
        }

        $this->getLocation();

        logger()->info(sprintf('TIMEZONE FALLBACK="%s" GUESSED="%s"', $timezone, $this->location['timezone']));

        $identifiers = timezone_identifiers_list();

        return in_array($this->location['timezone'], $identifiers, true)
            ? $this->location['timezone']
            : $timezone;
    }

    protected function getCountry(): ?string
    {
        $this->getLocation();

        return Arr::get($this->location, 'isoCode');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getLocation(): array
    {
        if ($this->location === null) {
            logger()->info('Getting location');

            $geoip = app('geoip');

            $this->location = $geoip->getLocation();

            logger()->info(serialize($this->location));
        }

        return $this->location;
    }
}
