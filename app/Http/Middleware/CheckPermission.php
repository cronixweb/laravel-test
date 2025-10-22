<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();

        if (!$user->isActive()) {
            return response()->json(['error' => 'Account inactive'], 403);
        }

        if (!$user->hasPermission($permission)) {
            return response()->json([
                'error' => 'Insufficient permissions. Required permission: ' . $permission
            ], 403);
        }

        return $next($request);
    }
}
