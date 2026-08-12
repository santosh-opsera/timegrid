<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Events\NewContactWasRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use Fenos\Notifynder\Facades\Notifynder;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

class ContactController extends Controller
{
    public function index(Business $business): Response
    {
        logger()->info(__METHOD__);

        $contacts = auth()->user()->contacts()
            ->whereHas('businesses', fn ($q) => $q->where('businesses.id', $business->id))
            ->with('businesses')
            ->get();

        return Inertia::render('Business/Contacts/Index', [
            'business' => $business,
            'contacts' => $contacts,
        ]);
    }

    public function create(Business $business): Response|RedirectResponse
    {
        logger()->info(__METHOD__);

        $user = auth()->user();

        $existingContact = $business->addressbook()->getRegisteredUserId($user->id);

        if (! $existingContact) {
            $existingContact = $business->addressbook()->getSubscribed($user->email);
        }

        if (! $existingContact) {
            $existingContact = $user->contacts()->first();
        }

        if (! $existingContact) {
            $contact = new Contact();

            return Inertia::render('Business/Contacts/Create', [
                'business' => $business,
                'contact'  => $contact,
            ]);
        }

        logger()->info("[ADVICE] Found existing contact contactId:{$existingContact->id}");

        $contact = $business->addressbook()->copyFrom($existingContact, $user->id);

        flash()->success(trans('user.contacts.msg.store.associated_existing_contact'));

        return redirect()->route('user.business.contact.show', [$business, $contact]);
    }

    public function store(Business $business, StoreContactRequest $request): RedirectResponse
    {
        logger()->info(__METHOD__);

        $validated = $request->validated();

        $businessName = $business->name;
        Notifynder::category('user.subscribedBusiness')
            ->from('App\Models\User', auth()->id())
            ->to('Timegridio\Concierge\Models\Business', $business->id)
            ->url('http://localhost')
            ->extra(compact('businessName'))
            ->send();

        $contact = $business->addressbook()->register($validated);

        $business->addressbook()->linkToUserId($contact, auth()->id());

        event(new NewContactWasRegistered($contact));

        flash()->success(trans('user.contacts.msg.store.success'));

        return redirect()->route('user.business.contact.show', [$business, $contact]);
    }

    public function show(Business $business, Contact $contact): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s contactId:%s', $business->id, $contact->id));

        $this->authorize('manage', $contact);

        $memberSince = $business->contacts()->find($contact->id)?->pivot->created_at;

        $appointments = $contact->appointments()
            ->with(['service', 'business'])
            ->orderBy('start_at')
            ->ofBusiness($business->id)
            ->active()
            ->get();

        return Inertia::render('Business/Contacts/Show', [
            'business'     => $business,
            'contact'      => $contact,
            'appointments' => $appointments,
            'memberSince'  => $memberSince,
        ]);
    }

    public function edit(Business $business, Contact $contact): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s contactId:%s', $business->id, $contact->id));

        $this->authorize('manage', $contact);

        return Inertia::render('Business/Contacts/Edit', [
            'business' => $business,
            'contact'  => $contact,
        ]);
    }

    public function update(Business $business, Contact $contact, UpdateContactRequest $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s contactId:%s', $business->id, $contact->id));

        $this->authorize('manage', $contact);

        $validated = $request->validated();
        $notes = $validated['notes'] ?? null;

        $data = collect($validated)->only([
            'firstname',
            'lastname',
            'email',
            'nin',
            'gender',
            'birthdate',
            'mobile',
            'mobile_country',
        ])->all();

        $contact = $business->addressbook()->update($contact, $data, $notes);

        flash()->success(trans('user.contacts.msg.update.success'));

        return redirect()->route('user.business.contact.show', [$business, $contact]);
    }

    public function destroy(Business $business, Contact $contact): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s contactId:%s', $business->id, $contact->id));

        $this->authorize('manage', $contact);

        $business->addressbook()->remove($contact);

        flash()->success(trans('user.contacts.msg.destroy.success'));

        return redirect()->route('user.business.contact.index', $business);
    }
}
