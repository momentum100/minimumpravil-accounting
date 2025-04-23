<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has either 'owner' or 'finance' role
        if (! $request->user() || ! in_array($request->user()->role, ['owner', 'finance'])) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
