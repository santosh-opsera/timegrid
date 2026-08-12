<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Events\NewAppointmentWasBooked;
use App\Events\NewSoftAppointmentWasBooked;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Concierge;
use Timegridio\Concierge\Exceptions\DuplicatedAppointmentException;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

class AgendaController extends Controller
{
    public function __construct(
        private readonly Concierge $concierge
    ) {}

    public function getIndex(): Response
    {
        logger()->info(__METHOD__);

        $appointments = auth()->user()
            ->appointments()
            ->with(['business', 'service', 'contact'])
            ->orderBy('start_at')
            ->unarchived()
            ->get();

        return Inertia::render('Appointments/Index', [
            'appointments' => $appointments,
        ]);
    }

    public function getAvailability(Business $business, Request $request): Response|RedirectResponse
    {
        logger()->info(__METHOD__);

        $contact = null;

        if (auth()->user()) {
            if ($behalfOfId = $request->input('behalfOfId')) {
                $this->authorize('manageContacts', $business);

                $contact = $business->contacts()->find($behalfOfId);
            } else {
                $contact = auth()->user()->getContactSubscribedTo($business->id);

                if (! $contact) {
                    $contact = $this->autoSubscribeUser(auth()->user(), $business);
                }
            }

            logger()->info('User checking vacancies', [
                'category'    => 'user.checkingVacancies',
                'user_id'     => auth()->id(),
                'business_id' => $business->id,
            ]);
        }

        $date = $request->input('date', 'today');
        $days = (int) $request->input('days', $business->pref('availability_future_days'));

        $startFromDate = $this->sanitizeDate($date);

        if ($startFromDate->isPast()) {
            $startFromDate = $this->sanitizeDate('today');
        }

        $includeToday = $business->pref('appointment_take_today');

        if ($startFromDate->isToday() && ! $includeToday) {
            $startFromDate = $this->sanitizeDate('tomorrow');
        }

        $availability = $this->concierge
            ->business($business)
            ->vacancies()
            ->generateAvailability($startFromDate->toDateString(), $days);

        $endDate = $startFromDate->copy()->addDays($days);

        $business->load(['services']);

        return Inertia::render('Booking/Book', [
            'business'      => $business,
            'availability'  => $availability,
            'startFromDate' => $startFromDate->toDateString(),
            'endDate'       => $endDate->toDateString(),
            'contact'       => $contact,
            'language'      => $this->getActiveLanguage($business->locale),
        ]);
    }

    public function postStore(StoreAppointmentRequest $request): Response|RedirectResponse
    {
        logger()->info(__METHOD__);

        $validated = $request->validated();

        $business = Business::with('services')->findOrFail($validated['businessId']);
        $email = $validated['email'] ?? null;
        $contactId = $validated['contact_id'] ?? null;
        $isOwner = false;

        $issuer = auth()->user();

        if ($issuer) {
            $isOwner = $issuer->isOwnerOf($business->id);
            $contact = $this->findSubscribedContact($issuer, $isOwner, $business, $contactId);
        } else {
            $contact = $this->getContact($business, $email);

            if (! $contact) {
                logger()->info('[ADVICE] Not subscribed');

                session()->flash('warning', trans('user.booking.msg.store.not-registered'));

                return redirect()->back();
            }

            auth()->once(['email' => $email]);
        }

        $service = $business->services()->findOrFail($validated['service_id']);

        $date = Carbon::parse($validated['_date'])->toDateString();
        $time = Carbon::parse($validated['_time'])->toTimeString();
        $timezone = $validated['_timezone'] ?? $business->timezone;
        $comments = $validated['comments'] ?? null;
        $issuerId = auth()->id();

        $reservation = compact('issuer', 'contact', 'service', 'date', 'time', 'timezone', 'comments');
        $reservation['issuer'] = $issuerId;

        logger()->info('Reservation submitted', [
            'business_id' => $business->id,
            'contact_id' => $contact?->id,
            'service_id' => $service->id,
            'user_id' => $issuerId,
            'date' => $date,
            'time' => $time,
            'timezone' => $timezone,
        ]);

        try {
            $appointment = $this->concierge->business($business)->takeReservation($reservation);
        } catch (DuplicatedAppointmentException $e) {
            $code = $this->concierge->appointment()->code;

            logger()->info("DUPLICATED Appointment with CODE:{$code}");

            session()->flash('warning', trans('user.booking.msg.store.sorry_duplicated', compact('code')));

            if ($isOwner) {
                return redirect()->route('manager.business.agenda.index', compact('business'));
            }

            return redirect()->route('user.agenda');
        }

        if ($appointment === false) {
            logger()->info('[ADVICE] Unable to book');

            session()->flash('warning', trans('user.booking.msg.store.error'));

            return redirect()->back();
        }

        $appointment->load(['business', 'service', 'contact']);

        logger()->info('Appointment saved successfully');

        session()->flash('success', trans('user.booking.msg.store.success', ['code' => $appointment->code]));

        if (! $issuerId) {
            event(new NewSoftAppointmentWasBooked($appointment));

            return Inertia::render('Booking/Show', [
                'appointment' => $appointment,
            ]);
        }

        event(new NewAppointmentWasBooked(auth()->user(), $appointment));

        if ($isOwner) {
            return redirect()->route('manager.business.agenda.index', compact('business'));
        }

        return redirect()->route('user.agenda', '#'.$appointment->code);
    }

    protected function getContact(Business $business, ?string $email): ?Contact
    {
        if ($business->pref('allow_guest_registration')) {
            return $business->addressbook()->register(compact('email'));
        }

        return $business->addressbook()->getSubscribed($email);
    }

    public function getValidate(Request $request, Business $business): Response|RedirectResponse
    {
        $validated = $request->validate([
            'code'  => ['required', 'string', 'min:4'],
            'email' => ['required', 'email'],
        ]);

        $code = $validated['code'];
        $email = $validated['email'];

        $appointment = $business->bookings()
            ->with(['contact', 'service', 'business'])
            ->where('hash', 'like', "{$code}%")
            ->whereHas('Contact', function ($q) use ($email): void {
                $q->where('email', $email);
            })->first();

        if (! $appointment) {
            session()->flash('error', trans('user.booking.msg.validate.error.no-appointment-was-found'));

            return redirect()->to('/');
        }

        if ($appointment->status == Appointment::STATUS_CONFIRMED) {
            session()->flash('success', trans('user.booking.msg.validate.success.your-appointment-is-already-confirmed'));

            return Inertia::render('Booking/Show', [
                'appointment' => $appointment,
            ]);
        }

        $appointment->doConfirm();

        session()->flash('success', trans('user.booking.msg.validate.success.your-appointment-was-confirmed'));

        return Inertia::render('Booking/Show', [
            'appointment' => $appointment,
        ]);
    }

    protected function findSubscribedContact(User $issuer, bool $isOwner, Business $business, ?int $contactId): ?Contact
    {
        if ($contactId && $isOwner) {
            return $business->contacts()->find($contactId);
        }

        return $issuer->getContactSubscribedTo($business->id);
    }

    protected function getActiveLanguage(string $locale): string
    {
        return session()->get('language', substr($locale, 0, 2));
    }

    protected function autoSubscribeUser(User $user, Business $business): Contact
    {
        $contact = Contact::query()->where('user_id', $user->id)->first();

        if (! $contact) {
            $nameParts = explode(' ', $user->name, 2);
            $contact = Contact::query()->create([
                'user_id'   => $user->id,
                'firstname' => $nameParts[0] ?? $user->name,
                'lastname'  => $nameParts[1] ?? '',
                'email'     => $user->email,
                'gender'    => 'X',
            ]);
        }

        if (! $contact->isSubscribedTo($business->id)) {
            $business->contacts()->attach($contact->id);
            $contact->load('businesses');
        }

        logger()->info('Auto-subscribed user to business', [
            'user_id'     => $user->id,
            'contact_id'  => $contact->id,
            'business_id' => $business->id,
        ]);

        return $contact;
    }

    protected function sanitizeDate(string $dateString): Carbon
    {
        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            logger()->warning('Unexpected date string: '.$dateString);

            return Carbon::now();
        }
    }
}
