<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions for each resource (these match shield:generate naming)
        $resources = [
            'parking::ticket' => ['view_any', 'view', 'create', 'update', 'delete', 'delete_any'],
            'vehicle::type' => ['view_any', 'view', 'create', 'update', 'delete', 'delete_any'],
            'user' => ['view_any', 'view', 'create', 'update', 'delete', 'delete_any'],
            'role' => ['view_any', 'view', 'create', 'update', 'delete', 'delete_any'],
        ];

        // Page permissions
        $pages = [
            'page_ManageGeneralSettings',
        ];

        // Widget permissions
        $widgets = [
            'widget_RevenueChart',
            'widget_VehicleTypeChart',
        ];

        // Create resource permissions
        foreach ($resources as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$resource}",
                    'guard_name' => 'web',
                ]);
            }
        }

        // Create page permissions
        foreach ($pages as $page) {
            Permission::firstOrCreate([
                'name' => $page,
                'guard_name' => 'web',
            ]);
        }

        // Create widget permissions
        foreach ($widgets as $widget) {
            Permission::firstOrCreate([
                'name' => $widget,
                'guard_name' => 'web',
            ]);
        }

        // Create Super Admin role with all permissions
        $superAdmin = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);
        $superAdmin->syncPermissions(Permission::all());

        // Create Staff role with limited permissions
        $staff = Role::firstOrCreate([
            'name' => 'staff',
            'guard_name' => 'web',
        ]);

        // Staff permissions - can manage parking tickets and view vehicle types
        $staffPermissions = Permission::whereIn('name', [
            'view_any_parking::ticket',
            'view_parking::ticket',
            'create_parking::ticket',
            'update_parking::ticket',
            'view_any_vehicle::type',
            'view_vehicle::type',
        ])->get();

        $staff->syncPermissions($staffPermissions);

        // Create Viewer role with read-only permissions
        $viewer = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'web',
        ]);

        $viewerPermissions = Permission::whereIn('name', [
            'view_any_parking::ticket',
            'view_parking::ticket',
            'view_any_vehicle::type',
            'view_vehicle::type',
        ])->get();

        $viewer->syncPermissions($viewerPermissions);
    }
}
