<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Application\Services\AuditService;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function __construct(
        private AuditService $auditService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log request
        $this->logRequest($request);

        $response = $next($request);

        // Log response
        $this->logResponse($request, $response);

        return $response;
    }

    /**
     * Log the request details.
     */
    private function logRequest(Request $request): void
    {
        try {
            $user = $request->user();

            $this->auditService->log([
                'user_id' => $user?->id,
                'action' => 'request',
                'model_type' => 'http_request',
                'model_id' => $request->header('X-Request-ID'),
                'old_data' => null,
                'new_data' => [
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'query_params' => $request->query(),
                    'headers' => $request->headers->all(),
                ],
                'metadata' => [
                    'url' => $request->fullUrl(),
                    'timestamp' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            // Silent fail for audit logging
            Log::error('Audit logging failed for request', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log the response details.
     */
    private function logResponse(Request $request, Response $response): void
    {
        try {
            $user = $request->user();

            $this->auditService->log([
                'user_id' => $user?->id,
                'action' => 'response',
                'model_type' => 'http_response',
                'model_id' => $request->header('X-Request-ID'),
                'old_data' => null,
                'new_data' => [
                    'status_code' => $response->getStatusCode(),
                    'content_type' => $response->headers->get('Content-Type'),
                ],
                'metadata' => [
                    'url' => $request->fullUrl(),
                    'timestamp' => now()->toISOString(),
                    'response_time' => microtime(true) - LARAVEL_START,
                ],
            ]);
        } catch (\Exception $e) {
            // Silent fail for audit logging
            Log::error('Audit logging failed for response', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
