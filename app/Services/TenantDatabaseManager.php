<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class TenantDatabaseManager
{
    protected DatabaseManager $databaseManager;
    protected array $tenantConnections = [];

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Set the database connection for a tenant.
     */
    public function setTenantConnection(Tenant $tenant): void
    {
        $connectionName = $this->getTenantConnectionName($tenant);
        
        if (!$this->connectionExists($connectionName)) {
            $this->createTenantConnection($tenant);
        }

        // Set the default connection to the tenant's database
        Config::set('database.default', $connectionName);
        DB::purge($connectionName);
        
        // Store tenant in app container for easy access
        app()->instance('tenant', $tenant);
        app()->instance('tenant.connection', $connectionName);
    }

    /**
     * Create a new database connection for a tenant.
     */
    public function createTenantConnection(Tenant $tenant): void
    {
        $connectionName = $this->getTenantConnectionName($tenant);
        $databaseName = $this->getTenantDatabaseName($tenant);
        
        $connectionConfig = [
            'driver' => config('database.connections.mysql.driver', 'mysql'),
            'host' => $tenant->database_host ?? config('database.connections.mysql.host'),
            'port' => $tenant->database_port ?? config('database.connections.mysql.port'),
            'database' => $databaseName,
            'username' => $tenant->database_username ?? config('database.connections.mysql.username'),
            'password' => $tenant->database_password ?? config('database.connections.mysql.password'),
            'charset' => config('database.connections.mysql.charset', 'utf8mb4'),
            'collation' => config('database.connections.mysql.collation', 'utf8mb4_unicode_ci'),
            'prefix' => config('database.connections.mysql.prefix', ''),
            'strict' => config('database.connections.mysql.strict', true),
            'engine' => config('database.connections.mysql.engine', null),
        ];

        Config::set("database.connections.{$connectionName}", $connectionConfig);
        
        $this->tenantConnections[$tenant->id] = $connectionName;
    }

    /**
     * Create the physical database for a tenant.
     */
    public function createTenantDatabase(Tenant $tenant): bool
    {
        try {
            $databaseName = $this->getTenantDatabaseName($tenant);
            $systemConnection = $this->getSystemConnection($tenant);
            
            // Create the database
            DB::connection($systemConnection)->statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to create database for tenant {$tenant->slug}: " . $e->getMessage());
        }
    }

    /**
     * Drop the physical database for a tenant.
     */
    public function dropTenantDatabase(Tenant $tenant): bool
    {
        try {
            $databaseName = $this->getTenantDatabaseName($tenant);
            $systemConnection = $this->getSystemConnection($tenant);
            
            // Drop the database
            DB::connection($systemConnection)->statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            
            // Remove the connection configuration
            $connectionName = $this->getTenantConnectionName($tenant);
            Config::forget("database.connections.{$connectionName}");
            
            if (isset($this->tenantConnections[$tenant->id])) {
                unset($this->tenantConnections[$tenant->id]);
            }
            
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to drop database for tenant {$tenant->slug}: " . $e->getMessage());
        }
    }

    /**
     * Test if a tenant database connection is working.
     */
    public function testTenantConnection(Tenant $tenant): bool
    {
        try {
            $this->createTenantConnection($tenant);
            $connectionName = $this->getTenantConnectionName($tenant);
            
            DB::connection($connectionName)->getPdo();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if a tenant database exists.
     */
    public function tenantDatabaseExists(Tenant $tenant): bool
    {
        try {
            $databaseName = $this->getTenantDatabaseName($tenant);
            $systemConnection = $this->getSystemConnection($tenant);
            
            $result = DB::connection($systemConnection)
                ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
            
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all tenant databases.
     */
    public function getAllTenantDatabases(): array
    {
        $databases = [];
        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $databases[] = [
                'tenant' => $tenant,
                'database_name' => $this->getTenantDatabaseName($tenant),
                'connection_name' => $this->getTenantConnectionName($tenant),
                'exists' => $this->tenantDatabaseExists($tenant),
            ];
        }
        
        return $databases;
    }

    /**
     * Reset to the default system connection.
     */
    public function resetToSystemConnection(): void
    {
        Config::set('database.default', 'mysql');
        app()->forgetInstance('tenant');
        app()->forgetInstance('tenant.connection');
    }

    /**
     * Get the connection name for a tenant.
     */
    protected function getTenantConnectionName(Tenant $tenant): string
    {
        return "tenant_{$tenant->id}";
    }

    /**
     * Get the database name for a tenant.
     */
    protected function getTenantDatabaseName(Tenant $tenant): string
    {
        $prefix = config('database.tenant_db_prefix', 'tenant_');
        return $prefix . $tenant->slug;
    }

    /**
     * Get the system connection name for database operations.
     */
    protected function getSystemConnection(Tenant $tenant): string
    {
        // Use the same connection config as tenant but without database name
        // This allows us to create/drop databases
        $systemConnectionName = "system_for_tenant_{$tenant->id}";
        
        if (!$this->connectionExists($systemConnectionName)) {
            $connectionConfig = [
                'driver' => config('database.connections.mysql.driver', 'mysql'),
                'host' => $tenant->database_host ?? config('database.connections.mysql.host'),
                'port' => $tenant->database_port ?? config('database.connections.mysql.port'),
                'database' => null, // No database specified for system operations
                'username' => $tenant->database_username ?? config('database.connections.mysql.username'),
                'password' => $tenant->database_password ?? config('database.connections.mysql.password'),
                'charset' => config('database.connections.mysql.charset', 'utf8mb4'),
                'collation' => config('database.connections.mysql.collation', 'utf8mb4_unicode_ci'),
                'prefix' => config('database.connections.mysql.prefix', ''),
                'strict' => config('database.connections.mysql.strict', true),
                'engine' => config('database.connections.mysql.engine', null),
            ];

            Config::set("database.connections.{$systemConnectionName}", $connectionConfig);
        }
        
        return $systemConnectionName;
    }

    /**
     * Check if a database connection exists.
     */
    protected function connectionExists(string $connectionName): bool
    {
        return config("database.connections.{$connectionName}") !== null;
    }
}
