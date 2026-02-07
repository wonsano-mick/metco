<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Check if user has 2FA enabled
        if ($user->hasTwoFactorEnabled()) {
            // Check if 2FA has been verified in this session
            $twoFactorVerified = $request->session()->get('2fa_verified', false);

            if (!$twoFactorVerified && !$request->is('api/v1/auth/two-factor*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'two_factor_required',
                    'message' => 'Two-factor authentication required',
                    'code' => '2FA_001',
                    'data' => [
                        'requires_2fa' => true,
                        'user_id' => $user->id,
                    ],
                ], 403);
            }
        }

        return $next($request);
    }
}
