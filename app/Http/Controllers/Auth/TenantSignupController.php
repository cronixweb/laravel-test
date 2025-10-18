<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantSignupRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TenantSignupController extends Controller
{
    public function create(): View
    {
        return view('auth.signup');
    }

    public function store(TenantSignupRequest $request): RedirectResponse
    {
        $data = $request->validated();

        [$tenant, $user] = DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['company_name'],
                'slug' => $data['tenant_slug'],
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'tenant_id' => $tenant->id,
                'role' => User::ROLE_ADMIN,
            ]);

            return [$tenant, $user];
        });

        Auth::login($user);

        $request->session()->regenerate();
        session([
            'tenant_id' => $tenant->id,
            'tenant_slug' => $tenant->slug,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('status', 'Your workspace is ready! Start tracking expenses for your team.');
    }
}
