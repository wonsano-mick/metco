<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract tenant from various sources
        $tenantId = $this->extractTenantId($request);

        if (!$tenantId) {
            return response()->json([
                'error' => 'tenant_required',
                'message' => 'Tenant identification is required',
                'code' => 'TENANT_001',
            ], 400);
        }

        // Set tenant context
        $request->merge(['tenant_id' => $tenantId]);

        // You might want to store this in a service container or context
        app()->instance('current_tenant_id', $tenantId);

        return $next($request);
    }

    /**
     * Extract tenant ID from request.
     */
    private function extractTenantId(Request $request): ?string
    {
        // Check in order of priority:

        // 1. From JWT token (if using token-based auth)
        if ($request->user()) {
            return $request->user()->tenant_id ?? null;
        }

        // 2. From header
        if ($request->hasHeader('X-Tenant-ID')) {
            return $request->header('X-Tenant-ID');
        }

        // 3. From query parameter
        if ($request->has('tenant_id')) {
            return $request->query('tenant_id');
        }

        // 4. From subdomain
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        if ($subdomain && $subdomain !== 'www' && $subdomain !== 'api') {
            return $subdomain;
        }

        return null;
    }
}
