<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdminOrFinance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // For API requests, returning JSON might be better:
            // if ($request->expectsJson()) {
            //     return response()->json(['message' => 'Unauthenticated.'], 401);
            // }
            return redirect('login');
        }

        $userRole = Auth::user()->role;
        $allowedRoles = ['admin', 'finance', 'owner']; // Added 'owner'

        if (!in_array($userRole, $allowedRoles)) {
            // If the user role is not in the allowed list, forbid access.
            abort(403, 'This action is unauthorized for your role.');
        }

        return $next($request);
    }
}
