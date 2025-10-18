<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantLoginRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(TenantLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $slug = Str::slug($credentials['tenant_slug']);

        $tenant = Tenant::query()->where('slug', $slug)->first();

        if (! $tenant) {
            return back()
                ->withErrors(['tenant_slug' => 'We could not find a tenant with that slug.'])
                ->onlyInput('tenant_slug');
        }

        $user = User::query()
            ->where('tenant_id', $tenant->id)
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'Invalid credentials for this tenant.'])
                ->onlyInput('tenant_slug', 'email');
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();
        session([
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

