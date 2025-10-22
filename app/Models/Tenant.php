<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'subdomain',
        'settings',
        'is_active',
        'trial_ends_at',
        'database_host',
        'database_port',
        'database_username',
        'database_password',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'database_port' => 'integer',
    ];

    protected $hidden = [
        'database_password',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name);
            }
        });
    }

    /**
     * Get the database name for this tenant.
     */
    public function getDatabaseName(): string
    {
        $prefix = config('database.tenant_db_prefix', 'tenant_');
        return $prefix . $this->slug;
    }

    /**
     * Get the connection name for this tenant.
     */
    public function getConnectionName(): string
    {
        return "tenant_{$this->id}";
    }

    /**
     * Check if tenant database exists.
     */
    public function databaseExists(): bool
    {
        return app(\App\Services\TenantDatabaseManager::class)->tenantDatabaseExists($this);
    }

    /**
     * Test database connection for this tenant.
     */
    public function testConnection(): bool
    {
        return app(\App\Services\TenantDatabaseManager::class)->testTenantConnection($this);
    }

    /**
     * Create database for this tenant.
     */
    public function createDatabase(): bool
    {
        return app(\App\Services\TenantDatabaseManager::class)->createTenantDatabase($this);
    }

    /**
     * Drop database for this tenant.
     */
    public function dropDatabase(): bool
    {
        return app(\App\Services\TenantDatabaseManager::class)->dropTenantDatabase($this);
    }

    /**
     * Check if tenant is active.
     */
    public function isActive(): bool
    {
        return $this->is_active && 
               ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    /**
     * Get tenant by domain or subdomain.
     */
    public static function findByDomain(string $domain): ?self
    {
        return static::where('domain', $domain)
                    ->orWhere('subdomain', $domain)
                    ->where('is_active', true)
                    ->first();
    }
}
