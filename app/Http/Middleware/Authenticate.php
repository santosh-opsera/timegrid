<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $guard = null): Response
    {
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->guest('/login');
        }

        return $next($request);
    }
}
