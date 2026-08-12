<?php

declare(strict_types=1);

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Logger as MonologLogger;

final class ConfigurePiiMasking
{
    public function __invoke(Logger $logger): void
    {
        $monolog = $logger->getLogger();

        if ($monolog instanceof MonologLogger) {
            $monolog->pushProcessor(new PiiMaskingProcessor());

            return;
        }

        if (method_exists($monolog, 'pushProcessor')) {
            $monolog->pushProcessor(new PiiMaskingProcessor());
        }
    }
}
