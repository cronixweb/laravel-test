<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            session()->forget(['tenant_id', 'tenant_slug']);

            return redirect()->route('login');
        }

        $tenant = Auth::user()->tenant;

        if (! $tenant) {
            Auth::logout();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Your account is missing a tenant association. Please contact support.',
                ]);
        }

        session([
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
        ]);

        app()->instance('tenant', $tenant);
        view()->share('tenant', $tenant);

        return $next($request);
    }
}

