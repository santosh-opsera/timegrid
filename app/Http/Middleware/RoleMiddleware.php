<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! auth()->check()) {
            return new RedirectResponse(url('/login'));
        }

        if (! $request->user()->hasRole($role)) {
            return new RedirectResponse(url('/'));
        }

        return $next($request);
    }
}
