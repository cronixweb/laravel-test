<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\TenantDatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class TenantMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:migrate 
                            {tenant? : The tenant slug to migrate (optional, migrates all if not provided)}
                            {--fresh : Drop all tables and re-run all migrations}
                            {--seed : Seed the database after migration}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for tenant databases';

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
        $fresh = $this->option('fresh');
        $seed = $this->option('seed');
        $force = $this->option('force');

        if ($tenantSlug) {
            $tenant = Tenant::where('slug', $tenantSlug)->first();
            if (!$tenant) {
                $this->error("Tenant with slug '{$tenantSlug}' not found.");
                return 1;
            }
            $this->migrateTenant($tenant, $fresh, $seed, $force);
        } else {
            $tenants = Tenant::where('is_active', true)->get();
            $this->info("Migrating " . $tenants->count() . " tenant databases...");
            
            foreach ($tenants as $tenant) {
                $this->migrateTenant($tenant, $fresh, $seed, $force);
            }
        }

        $this->info('Tenant migrations completed!');
        return 0;
    }

    protected function migrateTenant(Tenant $tenant, bool $fresh = false, bool $seed = false, bool $force = false): void
    {
        $this->info("Migrating tenant: {$tenant->name} ({$tenant->slug})");

        try {
            // Ensure tenant database exists
            if (!$tenant->databaseExists()) {
                $this->info("Creating database for tenant: {$tenant->slug}");
                $tenant->createDatabase();
            }

            // Set tenant connection
            $this->databaseManager->setTenantConnection($tenant);
            $connectionName = $tenant->getConnectionName();

            // Run migrations
            $migrationOptions = [
                '--database' => $connectionName,
                '--path' => 'database/migrations/tenant',
            ];

            if ($force) {
                $migrationOptions['--force'] = true;
            }

            if ($fresh) {
                $this->info("Running fresh migrations for tenant: {$tenant->slug}");
                Artisan::call('migrate:fresh', $migrationOptions);
            } else {
                $this->info("Running migrations for tenant: {$tenant->slug}");
                Artisan::call('migrate', $migrationOptions);
            }

            // Seed if requested
            if ($seed) {
                $this->info("Seeding database for tenant: {$tenant->slug}");
                Artisan::call('db:seed', [
                    '--database' => $connectionName,
                    '--class' => 'TenantDatabaseSeeder',
                    '--force' => $force,
                ]);
            }

            $this->info("✅ Successfully migrated tenant: {$tenant->slug}");

        } catch (\Exception $e) {
            $this->error("❌ Failed to migrate tenant {$tenant->slug}: " . $e->getMessage());
        } finally {
            // Reset to system connection
            $this->databaseManager->resetToSystemConnection();
        }
    }
}
