<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as BaseAuthenticate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate extends BaseAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // If you're using Sanctum, add this check
        if ($request->expectsJson()) {
            return $this->authenticateViaSanctum($request, $next, $guards);
        }

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Authenticate using Sanctum for API requests.
     */
    protected function authenticateViaSanctum($request, $next, array $guards)
    {
        try {
            $this->authenticate($request, $guards);
        } catch (AuthenticationException $e) {
            // For API requests, return JSON response
            return response()->json([
                'success' => false,
                'error' => 'unauthenticated',
                'message' => 'Authentication required',
                'code' => 'AUTH_401',
                'meta' => [
                    'timestamp' => now()->toISOString(),
                ],
            ], 401);
        }

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            return route('login');
        }

        return null;
    }
}
