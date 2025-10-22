<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $tenant = app('tenant');
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
        ]);

        $token = $user->createToken('auth-token', ['tenant:' . $tenant->id])->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->load('tenant'),
            'token' => $token,
        ], 201);
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $tenant = app('tenant');
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        $user = User::where('email', $request->email)
                   ->where('tenant_id', $tenant->id)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if (!$user->isActive()) {
            return response()->json(['error' => 'Account is inactive'], 403);
        }

        $token = $user->createToken('auth-token', ['tenant:' . $tenant->id])->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load('tenant', 'roles'),
            'token' => $token,
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('tenant', 'roles'),
        ]);
    }

    /**
     * Refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = app('tenant');

        // Delete current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('auth-token', ['tenant:' . $tenant->id])->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $token,
        ]);
    }
}
