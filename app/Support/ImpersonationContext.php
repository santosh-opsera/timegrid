<?php

declare(strict_types=1);

namespace App\Support;

use App\Http\Middleware\TrackImpersonation;

final class ImpersonationContext
{
    private static ?int $impersonatorId = null;

    public static function set(?int $impersonatorId): void
    {
        self::$impersonatorId = $impersonatorId;
    }

    public static function id(): ?int
    {
        return self::$impersonatorId ?? TrackImpersonation::impersonatorId();
    }

    public static function clear(): void
    {
        self::$impersonatorId = null;
    }
}
