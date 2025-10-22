<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();

        if (!$user->isActive()) {
            return response()->json(['error' => 'Account inactive'], 403);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (!$user->hasAnyRole($roles)) {
            return response()->json([
                'error' => 'Insufficient permissions. Required roles: ' . implode(', ', $roles)
            ], 403);
        }

        return $next($request);
    }
}
