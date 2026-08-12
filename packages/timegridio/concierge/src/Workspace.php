<?php

declare(strict_types=1);

namespace Timegridio\Concierge;

use Timegridio\Concierge\Models\Business;

class Workspace
{
    protected ?Business $business = null;

    protected ?string $timezone = null;

    public function business(Business $business): static
    {
        $this->business = $business;
        $this->timezone($this->business->timezone);

        return $this;
    }

    public function timezone(?string $timezone = null): static
    {
        if ($timezone !== null) {
            $this->timezone = $timezone;
        } elseif ($this->business !== null) {
            $this->timezone = $this->business->timezone;
        }

        return $this;
    }
}
