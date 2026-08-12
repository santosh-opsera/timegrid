<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVacancyRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Humanresource;
use Timegridio\Concierge\Models\Service;
use Timegridio\Concierge\Vacancy\VacancyParser;

class BusinessVacancyController extends Controller
{
    public function __construct(
        private readonly Concierge $concierge
    ) {}

    public function create(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageVacancies', $business);

        $business->load(['services', 'humanresources']);

        $daysQuantity = $business->pref('vacancy_edit_days_quantity', config('root.vacancy_edit_days'));

        $dates = $this->concierge
            ->business($business)
            ->vacancies()
            ->generateAvailability('today', $daysQuantity);

        if ($business->services->isEmpty()) {
            flash()->warning(trans('manager.vacancies.msg.edit.no_services'));
        }

        $advanced = $business->services->count() > 3 || $business->pref('vacancy_edit_advanced_mode');

        $template = $this->recallStatements($business->id);
        if ($advanced && empty($template)) {
            $template = $this->concierge
                ->vacancies()
                ->builder()
                ->getTemplate($business, $business->services()->first());
        }

        $servicesList = $business->services()->pluck('name', 'slug');
        $humanresourcesList = $business->humanresources()->pluck('name', 'slug');
        $weekdaysList = [
            'mon' => trans('datetime.weekday.monday'),
            'tue' => trans('datetime.weekday.tuesday'),
            'wed' => trans('datetime.weekday.wednesday'),
            'thu' => trans('datetime.weekday.thursday'),
            'fri' => trans('datetime.weekday.friday'),
            'sat' => trans('datetime.weekday.saturday'),
            'sun' => trans('datetime.weekday.sunday'),
        ];

        $startAt = Carbon::parse('today '.$business->pref('start_at').' '.$business->timezone)->format('h:i A');
        $finishAt = Carbon::parse('today '.$business->pref('finish_at').' '.$business->timezone)->format('h:i A');

        return Inertia::render('Business/Vacancies/Index', [
            'business'           => $business,
            'dates'              => $dates,
            'advanced'           => $advanced,
            'template'           => $template,
            'servicesList'       => $servicesList,
            'humanresourcesList' => $humanresourcesList,
            'weekdaysList'       => $weekdaysList,
            'startAt'            => $startAt,
            'finishAt'           => $finishAt,
            'editorData'         => [
                'services'       => $business->services->pluck('slug')->all(),
                'humanresources' => $business->humanresources->pluck('slug')->all(),
                'lang'           => $this->getActiveLanguage($business->locale),
            ],
            'mode' => 'create',
        ]);
    }

    public function store(Business $business, StoreVacancyRequest $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageVacancies', $business);

        $validated = $request->validated();
        $publishedVacancies = $validated['vacancy'];

        $changed = false;

        foreach ($publishedVacancies as $date => $vacancy) {
            foreach ($vacancy as $serviceId => $capacity) {
                $startAt = Carbon::parse($date.' '.$business->pref('start_at').' '.$business->timezone);
                $finishAt = Carbon::parse($date.' '.$business->pref('finish_at').' '.$business->timezone);

                if ($capacity === '') {
                    continue;
                }

                $changed = true;

                $this->concierge
                    ->business($business)
                    ->vacancies()
                    ->publish($date, $startAt, $finishAt, $serviceId, $capacity);
            }
        }

        if (! $changed) {
            logger()->warning('Nothing to update');

            flash()->warning(trans('manager.vacancies.msg.store.nothing_changed'));

            return redirect()->back();
        }

        logger()->info('Vacancies updated');

        flash()->success(trans('manager.vacancies.msg.store.success'));

        return redirect()->route('manager.business.show', [$business]);
    }

    public function storeBatch(Business $business, StoreVacancyRequest $request, VacancyParser $vacancyParser): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageVacancies', $business);

        $validated = $request->validated();

        $this->concierge->business($business);

        $statements = $validated['vacancies'];
        $unpublish = $validated['unpublish'] ?? false;

        if ($unpublish) {
            $this->concierge->vacancies()->unpublish();
        }

        $publishedVacancies = $vacancyParser->parseStatements($statements);

        if (! $this->concierge->vacancies()->updateBatch($business, $publishedVacancies)) {
            logger()->warning('Nothing to update');

            flash()->warning(trans('manager.vacancies.msg.store.nothing_changed'));

            return redirect()->back();
        }

        if ($request->boolean('remember')) {
            $this->rememberStatements($business->id, $statements);
        }

        logger()->info('Vacancies updated');

        flash()->success(trans('manager.vacancies.msg.store.success'));

        return redirect()->route('manager.business.show', [$business]);
    }

    public function show(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageVacancies', $business);

        $daysQuantity = $business->pref('vacancy_edit_days_quantity', config('root.vacancy_edit_days'));

        $vacancies = $business->vacancies()->with('appointments')->get();

        $timetable = $this->concierge
            ->business($business)
            ->timetable()
            ->buildTimetable($vacancies, 'today', $daysQuantity);

        if ($business->services()->count() === 0) {
            flash()->warning(trans('manager.vacancies.msg.edit.no_services'));
        }

        return Inertia::render('Business/Vacancies/Index', [
            'business'  => $business,
            'timetable' => $timetable,
            'mode'      => 'show',
        ]);
    }

    public function update(Business $business, StoreVacancyRequest $request, VacancyParser $vacancyParser): JsonResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageVacancies', $business);

        $validated = $request->validated();

        $serviceId = $validated['serviceId'];
        $weekdays = $validated['weekdays'];

        logger()->info(json_encode($weekdays));

        $service = $business->services()->findOrFail($serviceId);
        $humanResource = $business->humanresources()->firstOrFail();

        $startAt = $business->pref('start_at');
        $finishAt = $business->pref('finish_at');

        $statements = $this->buildStatements($service, $humanResource, $weekdays, $startAt, $finishAt, $business->timezone);

        $publishedVacancies = $vacancyParser->parseStatements($statements);

        $this->concierge->business($business);

        $business->vacancies()->where(['service_id' => $service->id])->delete();

        if ($this->concierge->vacancies()->updateBatch($business, $publishedVacancies)) {
            logger()->info('Vacancies updated');
        }

        return response()->json(['status' => 'OK']);
    }

    /**
     * @param  array<string, mixed>  $weekdays
     */
    protected function buildStatements(
        Service $service,
        Humanresource $humanResource,
        array $weekdays,
        string $startAt,
        string $finishAt,
        string $timezone
    ): string {
        $out = [];

        $out[] = "{$service->slug}:{$humanResource->slug}";
        $dates = [];
        foreach (array_keys($weekdays) as $day) {
            for ($i = 0; $i < 4; $i++) {
                $dates[] = Carbon::parse($day." +$i weeks ".$timezone)->toDateString();
            }
        }
        $out[] = ' '.implode(',', $dates);
        $out[] = "  {$startAt} - {$finishAt}";

        return implode("\n", $out);
    }

    protected function getActiveLanguage(string $locale): string
    {
        return session()->get('language', substr($locale, 0, 2));
    }

    protected function rememberStatements(int $businessId, string $statements): bool
    {
        return Storage::put(
            $this->getStatementsFile($businessId),
            $statements
        );
    }

    protected function recallStatements(int $businessId): ?string
    {
        if (! Storage::exists($this->getStatementsFile($businessId))) {
            return null;
        }

        return Storage::get($this->getStatementsFile($businessId));
    }

    protected function getStatementsFile(int $businessId): string
    {
        return 'business'.DIRECTORY_SEPARATOR.$businessId.DIRECTORY_SEPARATOR.'vacancy-statements.txt';
    }
}
