<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseFormatter
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only format JSON responses
        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            // If response is already formatted with our structure, leave it
            if (isset($data['success'])) {
                return $response;
            }

            // Format successful responses
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $formattedData = [
                    'success' => true,
                    'data' => $data,
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                        'request_id' => $request->header('X-Request-ID', uniqid()),
                        'version' => 'v1',
                    ],
                ];
            } else {
                // Format error responses
                $formattedData = [
                    'success' => false,
                    'error' => $this->getErrorCode($response->getStatusCode()),
                    'message' => $data['message'] ?? 'An error occurred',
                    'code' => $data['code'] ?? 'ERR_' . $response->getStatusCode(),
                    'meta' => [
                        'timestamp' => now()->toISOString(),
                        'request_id' => $request->header('X-Request-ID', uniqid()),
                    ],
                ];

                // Add validation errors if present
                if (isset($data['errors'])) {
                    $formattedData['errors'] = $data['errors'];
                }
            }

            $response->setData($formattedData);
        }

        // Add response headers
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('X-API-Version', 'v1');

        return $response;
    }

    /**
     * Get error code from HTTP status.
     */
    private function getErrorCode(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'bad_request',
            401 => 'unauthenticated',
            403 => 'forbidden',
            404 => 'not_found',
            422 => 'validation_error',
            429 => 'rate_limit_exceeded',
            500 => 'server_error',
            default => 'error',
        };
    }
}
