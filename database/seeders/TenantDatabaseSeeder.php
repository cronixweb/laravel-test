<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            ['name' => 'users.view', 'display_name' => 'View Users', 'description' => 'Can view users', 'group' => 'users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'description' => 'Can create users', 'group' => 'users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'description' => 'Can edit users', 'group' => 'users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'description' => 'Can delete users', 'group' => 'users'],
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'description' => 'Can view roles', 'group' => 'roles'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'description' => 'Can create roles', 'group' => 'roles'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Roles', 'description' => 'Can edit roles', 'group' => 'roles'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'description' => 'Can delete roles', 'group' => 'roles'],
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'description' => 'Can view settings', 'group' => 'settings'],
            ['name' => 'settings.edit', 'display_name' => 'Edit Settings', 'description' => 'Can edit settings', 'group' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Full access to all features',
                'is_active' => true,
            ]
        );

        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            [
                'name' => 'manager',
                'display_name' => 'Manager',
                'description' => 'Limited administrative access',
                'is_active' => true,
            ]
        );

        $userRole = Role::firstOrCreate(
            ['name' => 'user'],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Basic user access',
                'is_active' => true,
            ]
        );

        // Assign permissions to roles
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        $managerPermissions = Permission::whereIn('name', [
            'users.view', 'users.create', 'users.edit',
            'roles.view',
            'settings.view'
        ])->get();
        $managerRole->permissions()->sync($managerPermissions->pluck('id'));

        $userPermissions = Permission::whereIn('name', [
            'settings.view'
        ])->get();
        $userRole->permissions()->sync($userPermissions->pluck('id'));

        // Create demo users
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@demo.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
        $adminUser->roles()->sync([$adminRole->id]);

        $managerUser = User::firstOrCreate(
            ['email' => 'manager@demo.com'],
            [
                'name' => 'Manager User',
                'email' => 'manager@demo.com',
                'password' => Hash::make('password'),
                'role' => 'manager',
                'is_active' => true,
            ]
        );
        $managerUser->roles()->sync([$managerRole->id]);

        $regularUser = User::firstOrCreate(
            ['email' => 'user@demo.com'],
            [
                'name' => 'Regular User',
                'email' => 'user@demo.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
            ]
        );
        $regularUser->roles()->sync([$userRole->id]);

        $this->command->info('Tenant database seeded successfully!');
        $this->command->info('Demo users created:');
        $this->command->info('Admin: admin@demo.com / password');
        $this->command->info('Manager: manager@demo.com / password');
        $this->command->info('User: user@demo.com / password');
    }
}
