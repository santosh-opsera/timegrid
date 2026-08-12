<?php

declare(strict_types=1);

namespace Timegridio\Concierge\Presenters;

use Illuminate\Database\Eloquent\Model;

abstract class Presenter
{
    public function __construct(protected Model $resource) {}
}
