<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;

class TenantDatabaseCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:db:create 
                            {tenant : The tenant slug}
                            {--drop : Drop the database if it exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create database for a specific tenant';

    protected TenantDatabaseManager $databaseManager;

    public function __construct(TenantDatabaseManager $databaseManager)
    {
        parent::__construct();
        $this->databaseManager = $databaseManager;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenantSlug = $this->argument('tenant');
        $drop = $this->option('drop');

        $tenant = Tenant::where('slug', $tenantSlug)->first();
        if (!$tenant) {
            $this->error("Tenant with slug '{$tenantSlug}' not found.");
            return 1;
        }

        try {
            $databaseName = $tenant->getDatabaseName();

            // Drop database if requested
            if ($drop && $tenant->databaseExists()) {
                if ($this->confirm("Are you sure you want to drop the database '{$databaseName}'? This action cannot be undone.")) {
                    $this->info("Dropping database: {$databaseName}");
                    $tenant->dropDatabase();
                    $this->info("✅ Database dropped successfully.");
                } else {
                    $this->info("Database drop cancelled.");
                    return 0;
                }
            }

            // Create database
            if ($tenant->databaseExists()) {
                $this->info("Database '{$databaseName}' already exists.");
            } else {
                $this->info("Creating database: {$databaseName}");
                $tenant->createDatabase();
                $this->info("✅ Database created successfully.");
            }

            // Test connection
            if ($tenant->testConnection()) {
                $this->info("✅ Database connection test successful.");
            } else {
                $this->error("❌ Database connection test failed.");
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("❌ Failed to create database for tenant {$tenantSlug}: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
