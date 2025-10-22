<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\ResolveTenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::middleware([ResolveTenant::class])->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });
});

// Protected routes (authentication required)
Route::middleware(['auth:sanctum', ResolveTenant::class])->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/revoke-all', [AuthController::class, 'revokeAll']);
    });

    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
