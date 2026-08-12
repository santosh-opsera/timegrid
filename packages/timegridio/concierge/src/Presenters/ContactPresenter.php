<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Presenters;

use Carbon\Carbon;
use Timegridio\Concierge\Exceptions\InvalidContactAgeException;
use Timegridio\Concierge\Models\Contact;

class ContactPresenter extends Presenter
{
    public function __construct(Contact $resource)
    {
        parent::__construct($resource);
    }

    protected function contact(): Contact
    {
        /** @var Contact $resource */
        $resource = $this->resource;

        return $resource;
    }

    public function fullname(): string
    {
        return trim($this->contact()->firstname.' '.$this->contact()->lastname);
    }

    public function quality(): float
    {
        $propertiesScore = [
            'firstname' => 3,
            'lastname' => 7,
            'nin' => 10,
            'birthdate' => 5,
            'mobile' => 20,
            'email' => 15,
            'postal_address' => 15,
            'user' => 25,
        ];
        $totalScore = array_sum($propertiesScore);
        $qualityScore = 0;

        foreach ($propertiesScore as $property => $score) {
            $value = $this->contact()->{$property};

            if (trim((string) $value) !== '') {
                $qualityScore += $score;
            }
        }

        return ceil($qualityScore / $totalScore * 100);
    }

    public function age(): ?int
    {
        if ($this->contact()->birthdate === null) {
            return null;
        }

        $reference = Carbon::now();
        $born = Carbon::parse($this->contact()->birthdate);

        if ($born->greaterThan($reference)) {
            throw new InvalidContactAgeException;
        }

        return $born->diffInYears($reference);
    }
}
