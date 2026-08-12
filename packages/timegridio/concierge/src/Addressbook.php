<?php

declare(strict_types=1);

namespace Timegridio\Concierge;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Contact;

/**
 * Simplified contact repository with common read/write operations.
 */
class Addressbook
{
    public function __construct(
        private readonly Business $business,
    ) {}

    public function listing(int $limit): mixed
    {
        return $this->business->contacts()->orderBy('lastname')->simplePaginate($limit);
    }

    public function find(Contact $contact): ?Contact
    {
        return $this->business->contacts()->find($contact->id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function register(array $data): Contact
    {
        $contact = $this->getSubscribed((string) ($data['email'] ?? ''));

        if ($contact instanceof Contact) {
            return $contact;
        }

        $this->sanitizeDate($data['birthdate'] ?? null);

        $contact = Contact::query()->create($data);

        $this->business->contacts()->attach($contact, Arr::only($data, ['notes']));

        return $contact;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Contact $contact, array $data = [], ?string $notes = null): Contact
    {
        $birthdate = Arr::get($data, 'birthdate');
        $this->sanitizeDate($birthdate);

        $contact->fill(Arr::except($data, ['birthdate']));
        $contact->birthdate = $birthdate;
        $contact->save();

        $this->updateNotes($contact, $notes);

        return $contact;
    }

    public function getSubscribed(string $email): Contact|false
    {
        if (trim($email) === '') {
            return false;
        }

        return $this->business->contacts()->where('email', $email)->first();
    }

    public function getExisting(string $email): Contact|false
    {
        if (trim($email) === '') {
            return false;
        }

        return Contact::query()->whereNotNull('user_id')->where('email', $email)->first();
    }

    public function getRegisteredUserId(int $userId): ?Contact
    {
        return $this->business->contacts()->where('user_id', $userId)->first();
    }

    public function remove(Contact $contact): int
    {
        return $this->business->contacts()->detach($contact->id);
    }

    public function linkToUserId(Contact $contact, int $userId): Contact
    {
        $contact->user()->associate($userId);
        $contact->save();

        return $contact->fresh();
    }

    public function copyFrom(Contact $contact, int $userId): Contact
    {
        $replicatedContact = $contact->replicate(['id']);
        $replicatedContact->user()->associate($userId);
        $replicatedContact->businesses()->detach();
        $replicatedContact->save();

        $this->business->contacts()->attach($replicatedContact);
        $this->business->save();

        return $replicatedContact;
    }

    protected function updateNotes(Contact $contact, ?string $notes): void
    {
        $pivot = $this->business->contacts()->find($contact->id)?->pivot;

        if ($pivot !== null) {
            $pivot->update(['notes' => $notes]);
        }
    }

    protected function getDateFormat(): mixed
    {
        return $this->business->pref('date_format');
    }

    protected function sanitizeDate(mixed &$value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        if (trim($value) === '') {
            return $value = null;
        }

        if (strlen($value) === 19) {
            return $value = Carbon::parse($value);
        }

        if (strlen($value) === 10) {
            return $value = Carbon::createFromFormat('m/d/Y', $value);
        }

        return $value;
    }
}
