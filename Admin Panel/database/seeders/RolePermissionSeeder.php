<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * RolePermissionSeeder
 *
 * Seeds the roles (role table) and permissions (permissions table) used across
 * all Plantix AI panels.  Safe to re-run — uses firstOrCreate.
 *
 * Roles:
 *   super_admin     — full system access, all permissions
 *   admin           — manage products, orders, appointments, users, reports
 *   vendor_manager  — manage vendor onboarding, payouts, product approvals
 *   support         — read-only access + can update order status / returns
 */
class RolePermissionSeeder extends Seeder
{
    // ── Permission definitions ────────────────────────────────────────────────
    // Format: ['name' => string, 'group' => string, 'display_name' => string]
    private const PERMISSIONS = [
        // Users
        ['name' => 'users.view',           'group' => 'Users',        'display_name' => 'View Users'],
        ['name' => 'users.create',         'group' => 'Users',        'display_name' => 'Create Users'],
        ['name' => 'users.edit',           'group' => 'Users',        'display_name' => 'Edit Users'],
        ['name' => 'users.delete',         'group' => 'Users',        'display_name' => 'Delete Users'],
        ['name' => 'users.toggle_active',  'group' => 'Users',        'display_name' => 'Toggle User Active Status'],

        // Vendors
        ['name' => 'vendors.view',         'group' => 'Vendors',      'display_name' => 'View Vendors'],
        ['name' => 'vendors.approve',      'group' => 'Vendors',      'display_name' => 'Approve / Reject Vendors'],
        ['name' => 'vendors.delete',       'group' => 'Vendors',      'display_name' => 'Delete Vendors'],
        ['name' => 'vendors.payout',       'group' => 'Vendors',      'display_name' => 'Process Vendor Payouts'],

        // Products
        ['name' => 'products.view',        'group' => 'Products',     'display_name' => 'View Products'],
        ['name' => 'products.create',      'group' => 'Products',     'display_name' => 'Create Products'],
        ['name' => 'products.edit',        'group' => 'Products',     'display_name' => 'Edit Products'],
        ['name' => 'products.delete',      'group' => 'Products',     'display_name' => 'Delete Products'],
        ['name' => 'products.stock',       'group' => 'Products',     'display_name' => 'Manage Stock'],

        // Categories
        ['name' => 'categories.view',      'group' => 'Categories',   'display_name' => 'View Categories'],
        ['name' => 'categories.create',    'group' => 'Categories',   'display_name' => 'Create Categories'],
        ['name' => 'categories.edit',      'group' => 'Categories',   'display_name' => 'Edit Categories'],
        ['name' => 'categories.delete',    'group' => 'Categories',   'display_name' => 'Delete Categories'],

        // Orders
        ['name' => 'orders.view',          'group' => 'Orders',       'display_name' => 'View Orders'],
        ['name' => 'orders.update_status', 'group' => 'Orders',       'display_name' => 'Update Order Status'],
        ['name' => 'orders.cancel',        'group' => 'Orders',       'display_name' => 'Cancel Orders'],
        ['name' => 'orders.refund',        'group' => 'Orders',       'display_name' => 'Process Refunds'],

        // Returns
        ['name' => 'returns.view',         'group' => 'Returns',      'display_name' => 'View Return Requests'],
        ['name' => 'returns.approve',      'group' => 'Returns',      'display_name' => 'Approve Returns'],
        ['name' => 'returns.reject',       'group' => 'Returns',      'display_name' => 'Reject Returns'],

        // Appointments
        ['name' => 'appointments.view',    'group' => 'Appointments', 'display_name' => 'View Appointments'],
        ['name' => 'appointments.manage',  'group' => 'Appointments', 'display_name' => 'Manage All Appointments'],

        // Experts
        ['name' => 'experts.view',         'group' => 'Experts',      'display_name' => 'View Experts'],
        ['name' => 'experts.approve',      'group' => 'Experts',      'display_name' => 'Approve Expert Profiles'],
        ['name' => 'experts.delete',       'group' => 'Experts',      'display_name' => 'Delete Experts'],

        // Reports / Analytics
        ['name' => 'reports.sales',        'group' => 'Reports',      'display_name' => 'View Sales Reports'],
        ['name' => 'reports.users',        'group' => 'Reports',      'display_name' => 'View User Reports'],
        ['name' => 'reports.disease',      'group' => 'Reports',      'display_name' => 'View Disease Detection Reports'],

        // Settings
        ['name' => 'settings.view',        'group' => 'Settings',     'display_name' => 'View Settings'],
        ['name' => 'settings.edit',        'group' => 'Settings',     'display_name' => 'Edit Settings'],

        // Email Templates
        ['name' => 'email_templates.view', 'group' => 'Email',        'display_name' => 'View Email Templates'],
        ['name' => 'email_templates.edit', 'group' => 'Email',        'display_name' => 'Edit Email Templates'],

        // Disease detection (admin review)
        ['name' => 'disease.review',       'group' => 'Disease',      'display_name' => 'Review Manual Disease Reports'],
        ['name' => 'disease.assign',       'group' => 'Disease',      'display_name' => 'Assign Disease to Reports'],

        // Forum moderation
        ['name' => 'forum.moderate',       'group' => 'Forum',        'display_name' => 'Moderate Forum Threads'],
        ['name' => 'forum.delete',         'group' => 'Forum',        'display_name' => 'Delete Forum Threads / Replies'],
    ];

    // ── Role → permissions mapping ────────────────────────────────────────────
    private const ROLE_PERMISSIONS = [
        'super_admin' => '*', // all permissions
        'admin'       => [
            'users.view', 'users.create', 'users.edit', 'users.toggle_active',
            'vendors.view', 'vendors.approve',
            'products.view', 'products.create', 'products.edit', 'products.delete', 'products.stock',
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            'orders.view', 'orders.update_status', 'orders.cancel', 'orders.refund',
            'returns.view', 'returns.approve', 'returns.reject',
            'appointments.view', 'appointments.manage',
            'experts.view', 'experts.approve',
            'reports.sales', 'reports.users', 'reports.disease',
            'settings.view', 'settings.edit',
            'email_templates.view', 'email_templates.edit',
            'disease.review', 'disease.assign',
            'forum.moderate', 'forum.delete',
        ],
        'vendor_manager' => [
            'vendors.view', 'vendors.approve', 'vendors.delete', 'vendors.payout',
            'products.view', 'products.create', 'products.edit', 'products.stock',
            'categories.view',
            'orders.view',
            'reports.sales',
        ],
        'support' => [
            'users.view',
            'orders.view', 'orders.update_status',
            'returns.view', 'returns.approve', 'returns.reject',
            'appointments.view',
            'reports.sales',
            'disease.review',
            'forum.moderate',
        ],
    ];

    public function run(): void
    {
        // 1. Seed all permissions
        $permissionObjects = [];
        foreach (self::PERMISSIONS as $perm) {
            $permissionObjects[$perm['name']] = Permission::firstOrCreate(
                ['name' => $perm['name']],
                ['group' => $perm['group'], 'display_name' => $perm['display_name']]
            );
        }

        // 2. Seed roles and attach permissions
        $roleDefinitions = [
            ['role_name' => 'super_admin',    'guard' => 'admin', 'is_active' => true],
            ['role_name' => 'admin',          'guard' => 'admin', 'is_active' => true],
            ['role_name' => 'vendor_manager', 'guard' => 'admin', 'is_active' => true],
            ['role_name' => 'support',        'guard' => 'admin', 'is_active' => true],
        ];

        foreach ($roleDefinitions as $roleDef) {
            $role = Role::firstOrCreate(
                ['role_name' => $roleDef['role_name']],
                ['guard' => $roleDef['guard'], 'is_active' => $roleDef['is_active']]
            );

            $assignments = self::ROLE_PERMISSIONS[$roleDef['role_name']];

            if ($assignments === '*') {
                // super_admin gets every permission
                $role->permissions()->sync(array_column(array_values($permissionObjects), 'id'));
            } else {
                $ids = array_filter(
                    array_map(fn ($n) => $permissionObjects[$n]->id ?? null, $assignments)
                );
                $role->permissions()->sync($ids);
            }
        }

        $this->command->info('Roles and permissions seeded successfully.');
    }
}
