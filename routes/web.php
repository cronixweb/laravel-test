<?php

use App\Http\Controllers\Auth\TenantLoginController;
use App\Http\Controllers\Auth\TenantSignupController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/signup', [TenantSignupController::class, 'create'])->name('signup');
    Route::post('/signup', [TenantSignupController::class, 'store'])->name('signup.store');

    Route::get('/login', [TenantLoginController::class, 'create'])->name('login');
    Route::post('/login', [TenantLoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [TenantLoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/dashboard', [ExpenseController::class, 'index'])->name('dashboard');
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
});
