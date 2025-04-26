<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateInternalApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = $request->bearerToken(); // Expecting key in Authorization: Bearer <key>
        // Or use a custom header: $providedKey = $request->header('X-Internal-Api-Key');

        $validKey = config('services.internal_api.key');

        if (empty($validKey) || empty($providedKey) || !hash_equals($validKey, $providedKey)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Optional: You could fetch the associated user ID here and add it to the request
        // $request->attributes->set('api_user_id', config('services.internal_api.user_id'));

        return $next($request);
    }
}
