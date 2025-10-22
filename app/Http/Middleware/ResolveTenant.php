<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant not found or inactive'
            ], 404);
        }

        // Store tenant in request for later use
        $request->merge(['tenant' => $tenant]);
        
        // Set tenant in app container
        app()->instance('tenant', $tenant);

        return $next($request);
    }

    /**
     * Resolve tenant from request.
     */
    private function resolveTenant(Request $request): ?Tenant
    {
        // Try to resolve from subdomain first
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);
        
        if ($subdomain) {
            $tenant = Tenant::findByDomain($subdomain);
            if ($tenant) {
                return $tenant;
            }
        }

        // Try to resolve from full domain
        $tenant = Tenant::findByDomain($host);
        if ($tenant) {
            return $tenant;
        }

        // Try to resolve from header (for API requests)
        $tenantId = $request->header('X-Tenant-ID');
        if ($tenantId) {
            return Tenant::where('id', $tenantId)
                        ->where('is_active', true)
                        ->first();
        }

        // Try to resolve from slug in URL path
        $pathSegments = explode('/', trim($request->getPathInfo(), '/'));
        if (!empty($pathSegments[0])) {
            return Tenant::where('slug', $pathSegments[0])
                        ->where('is_active', true)
                        ->first();
        }

        return null;
    }

    /**
     * Extract subdomain from host.
     */
    private function extractSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);
        
        // If we have more than 2 parts, the first part is likely a subdomain
        if (count($parts) > 2) {
            return $parts[0];
        }

        return null;
    }
}
