<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user via API.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $tenant = app('tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found',
            ], 404);
        }

        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'user',
        ]);

        $token = $user->createToken('api-token', ['tenant:' . $tenant->id])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user->load('tenant'),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Login user via API.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $tenant = app('tenant');
        
        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found',
            ], 404);
        }

        $user = User::where('email', $request->email)
                   ->where('tenant_id', $tenant->id)
                   ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (!$user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Account is inactive',
            ], 403);
        }

        // Revoke existing tokens if needed
        if ($request->revoke_existing_tokens) {
            $user->tokens()->delete();
        }

        $token = $user->createToken('api-token', ['tenant:' . $tenant->id])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user->load('tenant', 'roles'),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout user via API.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user via API.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $request->user()->load('tenant', 'roles'),
            ],
        ]);
    }

    /**
     * Refresh token via API.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $tenant = app('tenant');

        // Delete current token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $user->createToken('api-token', ['tenant:' . $tenant->id])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token,
            ],
        ]);
    }

    /**
     * Revoke all tokens for the user.
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All tokens revoked successfully',
        ]);
    }
}
