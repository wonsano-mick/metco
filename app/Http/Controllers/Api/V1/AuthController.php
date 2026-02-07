<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Application\Services\AuthService;
use App\Http\Resources\Auth\LoginResource;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Requests\Auth\TwoFactorRequest;
use App\Http\Resources\Auth\RegisterResource;
use App\Http\Requests\Auth\RefreshTokenRequest;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register(
                $request->validated(),
                $request->ip(),
                $request->userAgent()
            );

            return response()->json(
                new RegisterResource($result),
                201,
                ['X-Request-ID' => $request->header('X-Request-ID', uniqid())]
            );
        } catch (\Exception $e) {
            return $this->handleError($e, 'REG_001');
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $throttleKey = 'login:' . $request->ip() . '|' . $request->input('email');

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json([
                'error' => 'rate_limit_exceeded',
                'message' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
                'code' => 'AUTH_002',
            ], 429);
        }

        try {
            $result = $this->authService->login(
                $request->input('email'),
                $request->input('password'),
                $request->input('device_id'),
                $request->input('device_info', []),
                $request->ip(),
                $request->userAgent()
            );

            RateLimiter::clear($throttleKey);

            return response()->json(
                new LoginResource($result),
                200,
                [
                    'X-Request-ID' => $request->header('X-Request-ID', uniqid()),
                    'X-2FA-Required' => $result['requires_2fa'] ? 'true' : 'false',
                ]
            );
        } catch (\Exception $e) {
            RateLimiter::hit($throttleKey);
            return $this->handleError($e, 'AUTH_001');
        }
    }

    public function twoFactorVerify(TwoFactorRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->authService->verifyTwoFactor(
                $user->id,
                $request->input('code'),
                $request->input('device_id')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'verified' => true,
                    'session_id' => $result['session_id'],
                ],
                'meta' => [
                    'request_id' => $request->header('X-Request-ID', uniqid()),
                    'timestamp' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, '2FA_001');
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout(
                $request->user()->id,
                $request->header('X-Device-ID'),
                $request->ip()
            );

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
                'meta' => [
                    'request_id' => $request->header('X-Request-ID', uniqid()),
                    'timestamp' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, 'AUTH_003');
        }
    }

    public function refresh(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->refreshToken(
                $request->input('refresh_token'),
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'access_token' => $result['access_token'],
                    'refresh_token' => $result['refresh_token'],
                    'expires_in' => $result['expires_in'],
                    'token_type' => 'Bearer',
                ],
                'meta' => [
                    'request_id' => $request->header('X-Request-ID', uniqid()),
                    'timestamp' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, 'AUTH_004');
        }
    }

    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $this->authService->getProfile($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->getId()->toString(),
                    'email' => $user->getEmail()->getValue(),
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'phone' => $user->getPhone() ? $user->getPhone()->getValue() : null,
                    'email_verified' => $user->isEmailVerified(),
                    'phone_verified' => $user->isPhoneVerified(),
                    'two_factor_enabled' => $user->hasTwoFactorEnabled(),
                    'last_login_at' => $user->getLastLoginAt()?->format('Y-m-d H:i:s'),
                    'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                ],
                'meta' => [
                    'request_id' => $request->header('X-Request-ID', uniqid()),
                    'timestamp' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleError($e, 'PROF_001');
        }
    }

    private function handleError(\Exception $e, string $code): JsonResponse
    {
        $statusCode = 500;

        // Map exception types to HTTP status codes
        if ($e instanceof \DomainException) {
            $statusCode = 400;
        } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
            $statusCode = 422;
        } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            $statusCode = 403;
        } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $statusCode = 404;
        }

        $message = config('app.debug') ? $e->getMessage() : 'An error occurred';

        Log::error('API Error', [
            'code' => $code,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => config('app.debug') ? $e->getTraceAsString() : null,
        ]);

        return response()->json([
            'error' => strtolower(str_replace('_', '-', $code)),
            'message' => $message,
            'code' => $code,
            'meta' => [
                'timestamp' => now()->toISOString(),
                'reference' => uniqid(),
            ],
        ], $statusCode);
    }
}
