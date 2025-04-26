<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirect buyers to their specific dashboard
                if ($user->role === 'buyer') {
                    return redirect()->route('buyer.dashboard');
                }

                // Redirect admins to their specific dashboard (optional, add if needed)
                // if ($user->role === 'admin') {
                //     return redirect()->route('admin.dashboard');
                // }

                // Default redirect for other authenticated users
                return redirect(config('fortify.home', '/dashboard'));
            }
        }

        return $next($request);
    }
}