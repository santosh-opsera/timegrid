<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index(Business $business)
    {
        $this->authorize('manage', $business);
        $contacts = $business->contacts()->with('user')->latest()->paginate(20);
        return Inertia::render('Business/Contacts/Index', [
            'business' => $business,
            'contacts' => $contacts,
        ]);
    }

    public function store(Request $request, Business $business)
    {
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $business->contacts()->create($validated);
        return back()->with('success', 'Contact added!');
    }

    public function update(Request $request, Business $business, Contact $contact)
    {
        $this->authorize('manage', $business);

        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
        ]);

        $contact->update($validated);
        return back()->with('success', 'Contact updated!');
    }

    public function destroy(Business $business, Contact $contact)
    {
        $this->authorize('manage', $business);
        $contact->delete();
        return back()->with('success', 'Contact removed.');
    }
}
