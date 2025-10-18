<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TenantAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_signup_creates_tenant_and_admin_user(): void
    {
        $response = $this->post(route('signup.store'), [
            'company_name' => 'Acme Corporation',
            'tenant_slug' => 'acme-corp',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'super-secret',
            'password_confirmation' => 'super-secret',
        ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('tenants', [
            'name' => 'Acme Corporation',
            'slug' => 'acme-corp',
        ]);

        $tenant = Tenant::where('slug', 'acme-corp')->first();
        $this->assertNotNull($tenant);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'tenant_id' => $tenant->id,
            'role' => 'admin',
        ]);

        $admin = User::where('email', 'jane@example.com')->first();
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_requires_matching_tenant_slug(): void
    {
        $tenantOne = Tenant::factory()->create(['slug' => 'acme']);
        $tenantTwo = Tenant::factory()->create(['slug' => 'beta']);

        $userOne = User::factory()->for($tenantOne)->admin()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('secret123'),
        ]);

        $userTwo = User::factory()->for($tenantTwo)->admin()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('anotherpass'),
        ]);

        $response = $this->post(route('login.store'), [
            'tenant_slug' => 'acme',
            'email' => 'owner@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($userOne);

        auth()->logout();

        $response = $this->post(route('login.store'), [
            'tenant_slug' => 'beta',
            'email' => 'owner@example.com',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $response = $this->post(route('login.store'), [
            'tenant_slug' => 'beta',
            'email' => 'owner@example.com',
            'password' => 'anotherpass',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($userTwo);
    }
}

