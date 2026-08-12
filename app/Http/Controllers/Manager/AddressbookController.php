<?php

declare(strict_types=1);

namespace App\Http\Controllers\Manager;

use App\Events\NewContactWasRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

class AddressbookController extends Controller
{
    public function index(Business $business): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageContacts', $business);

        $contacts = $business->addressbook()->listing(100);

        return Inertia::render('Business/Contacts/Index', [
            'business' => $business,
            'contacts' => $contacts,
        ]);
    }

    public function create(Business $business): Response|RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        if ($business->contacts()->count() > plan('limits.contacts', $business->plan)) {
            session()->flash('warning', trans('app.saas.plan_limit_reached'));

            return redirect()->back();
        }

        $this->authorize('manageContacts', $business);

        $contact = new Contact();

        return Inertia::render('Business/Contacts/Create', [
            'business' => $business,
            'contact'  => $contact,
        ]);
    }

    public function store(Business $business, StoreContactRequest $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s', $business->id));

        $this->authorize('manageContacts', $business);

        $validated = $request->validated();

        $contact = $business->addressbook()->register($validated);

        if (! $contact->wasRecentlyCreated) {
            session()->flash('warning', trans('manager.contacts.msg.store.warning_showing_existing_contact'));

            return redirect()->route('manager.addressbook.show', [$business, $contact]);
        }

        event(new NewContactWasRegistered($contact));

        session()->flash('success', trans('manager.contacts.msg.store.success'));

        return redirect()->route('manager.addressbook.show', [$business, $contact]);
    }

    public function show(Business $business, Contact $contact): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s contactId:%s', $business->id, $contact->id));

        $this->authorize('manageContacts', $business);

        $contact = $business->addressbook()->find($contact);

        return Inertia::render('Business/Contacts/Show', [
            'business' => $business,
            'contact'  => $contact,
        ]);
    }

    public function edit(Business $business, Contact $contact): Response
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s contactId:%s', $business->id, $contact->id));

        $this->authorize('manageContacts', $business);

        $contact = $business->addressbook()->find($contact);
        $notes = $contact->pivot->notes;

        return Inertia::render('Business/Contacts/Edit', [
            'business' => $business,
            'contact'  => $contact,
            'notes'    => $notes,
        ]);
    }

    public function update(Business $business, Contact $contact, UpdateContactRequest $request): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s contactId:%s', $business->id, $contact->id));

        $this->authorize('manageContacts', $business);

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
            'postal_address',
        ])->all();

        $contact = $business->addressbook()->update($contact, $data, $notes);

        session()->flash('success', trans('manager.contacts.msg.update.success'));

        return redirect()->route('manager.addressbook.show', [$business, $contact]);
    }

    public function destroy(Business $business, Contact $contact): RedirectResponse
    {
        logger()->info(__METHOD__);
        logger()->info(sprintf('businessId:%s contactId:%s', $business->id, $contact->id));

        $this->authorize('manageContacts', $business);

        $business->addressbook()->remove($contact);

        session()->flash('success', trans('manager.contacts.msg.destroy.success'));

        return redirect()->route('manager.addressbook.index', $business);
    }
}
