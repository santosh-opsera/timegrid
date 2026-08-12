<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ImpersonationContext;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackImpersonation
{
    public const SESSION_IMPERSONATOR_ID = 'impersonator_id';

    public const SESSION_IMPERSONATED_USER_ID = 'impersonated_user_id';

    public const SESSION_STARTED_AT = 'impersonation_started_at';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('logout') && $this->isImpersonating()) {
            $this->endImpersonation('logout');
        }

        if ($this->isImpersonating()) {
            if ($this->hasExpired()) {
                $this->endImpersonation('timeout');

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()
                    ->route('login')
                    ->with('error', 'Impersonation session expired.');
            }

            ImpersonationContext::set($this->impersonatorId());
        }

        return $next($request);
    }

    public static function begin(User|int $impersonator, User|int $impersonated): void
    {
        $impersonatorId = $impersonator instanceof User ? $impersonator->id : $impersonator;
        $impersonatedId = $impersonated instanceof User ? $impersonated->id : $impersonated;

        session([
            self::SESSION_IMPERSONATOR_ID => $impersonatorId,
            self::SESSION_IMPERSONATED_USER_ID => $impersonatedId,
            self::SESSION_STARTED_AT => now()->timestamp,
        ]);

        ImpersonationContext::set($impersonatorId);

        ActivityLog::query()->create([
            'log_name' => 'impersonation',
            'description' => 'impersonation.started',
            'subject_type' => User::class,
            'subject_id' => $impersonatedId,
            'causer_type' => User::class,
            'causer_id' => $impersonatedId,
            'impersonator_id' => $impersonatorId,
            'properties' => [
                'impersonated_user_id' => $impersonatedId,
            ],
        ]);

        logger()->warning('Impersonation session started', [
            'impersonator_id' => $impersonatorId,
            'impersonated_user_id' => $impersonatedId,
        ]);
    }

    public static function endImpersonation(string $reason = 'manual'): void
    {
        if (! session()->has(self::SESSION_IMPERSONATOR_ID)) {
            return;
        }

        $impersonatorId = (int) session(self::SESSION_IMPERSONATOR_ID);
        $impersonatedId = session(self::SESSION_IMPERSONATED_USER_ID);

        ActivityLog::query()->create([
            'log_name' => 'impersonation',
            'description' => 'impersonation.ended',
            'subject_type' => User::class,
            'subject_id' => $impersonatedId,
            'causer_type' => User::class,
            'causer_id' => $impersonatedId,
            'impersonator_id' => $impersonatorId,
            'properties' => [
                'reason' => $reason,
                'impersonated_user_id' => $impersonatedId,
            ],
        ]);

        logger()->warning('Impersonation session ended', [
            'impersonator_id' => $impersonatorId,
            'impersonated_user_id' => $impersonatedId,
            'reason' => $reason,
        ]);

        session()->forget([
            self::SESSION_IMPERSONATOR_ID,
            self::SESSION_IMPERSONATED_USER_ID,
            self::SESSION_STARTED_AT,
        ]);

        ImpersonationContext::clear();
    }

    public static function isImpersonating(): bool
    {
        return session()->has(self::SESSION_IMPERSONATOR_ID);
    }

    public static function impersonatorId(): ?int
    {
        $id = session(self::SESSION_IMPERSONATOR_ID);

        return $id !== null ? (int) $id : null;
    }

    private function hasExpired(): bool
    {
        $startedAt = session(self::SESSION_STARTED_AT);

        if ($startedAt === null) {
            return false;
        }

        $maxMinutes = (int) config('audit.impersonation_max_minutes', env('IMPERSONATION_MAX_MINUTES', 30));

        $started = Carbon::createFromTimestamp((int) $startedAt);

        return now()->diffInMinutes($started) >= $maxMinutes;
    }
}
