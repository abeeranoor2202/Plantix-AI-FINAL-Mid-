<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminRbacSeeder extends Seeder
{
    /**
     * All permissions derived directly from routes/panels/admin.php.
     * Format: ['name' => 'slug', 'group' => 'group-key', 'display_name' => 'Human Name']
     */
    private array $permissions = [
        // ── Users ──────────────────────────────────────────────────────────────
        ['name' => 'users',        'group' => 'users', 'display_name' => 'View Users'],
        ['name' => 'users.edit',   'group' => 'users', 'display_name' => 'Edit Users'],
        ['name' => 'users.create', 'group' => 'users', 'display_name' => 'Create Users'],
        ['name' => 'users.view',   'group' => 'users', 'display_name' => 'View User Detail'],

        // ── Admin Users ────────────────────────────────────────────────────────
        ['name' => 'admin.users',        'group' => 'admins', 'display_name' => 'View Admin Users'],
        ['name' => 'admin.users.create', 'group' => 'admins', 'display_name' => 'Create Admin Users'],
        ['name' => 'admin.users.store',  'group' => 'admins', 'display_name' => 'Store Admin Users'],
        ['name' => 'admin.users.delete', 'group' => 'admins', 'display_name' => 'Delete Admin Users'],
        ['name' => 'admin.users.edit',   'group' => 'admins', 'display_name' => 'Edit Admin Users'],
        ['name' => 'admin.users.update', 'group' => 'admins', 'display_name' => 'Update Admin Users'],

        // ── Vendors ────────────────────────────────────────────────────────────
        ['name' => 'vendors',              'group' => 'vendors',         'display_name' => 'View Vendors'],
        ['name' => 'approve.vendors.list', 'group' => 'approve_vendors', 'display_name' => 'Approve Vendors'],
        ['name' => 'pending.vendors.list', 'group' => 'pending_vendors', 'display_name' => 'Pending Vendors'],
        ['name' => 'vendor.document.list', 'group' => 'vendors-document', 'display_name' => 'List Vendor Documents'],
        ['name' => 'vendor.document.edit', 'group' => 'vendors-document', 'display_name' => 'Edit Vendor Documents'],

        // ── Stores ─────────────────────────────────────────────────────────────
        ['name' => 'stores',        'group' => 'stores', 'display_name' => 'View Stores'],
        ['name' => 'stores.create', 'group' => 'stores', 'display_name' => 'Create Stores'],
        ['name' => 'stores.edit',   'group' => 'stores', 'display_name' => 'Edit Stores'],
        ['name' => 'stores.view',   'group' => 'stores', 'display_name' => 'View Store Detail'],

        // ── Drivers ────────────────────────────────────────────────────────────
        ['name' => 'drivers',              'group' => 'drivers',         'display_name' => 'View Drivers'],
        ['name' => 'drivers.create',       'group' => 'drivers',         'display_name' => 'Create Drivers'],
        ['name' => 'drivers.edit',         'group' => 'drivers',         'display_name' => 'Edit Drivers'],
        ['name' => 'drivers.view',         'group' => 'drivers',         'display_name' => 'View Driver Detail'],
        ['name' => 'approve.driver.list',  'group' => 'approve_drivers', 'display_name' => 'Approve Drivers'],
        ['name' => 'pending.driver.list',  'group' => 'pending_drivers', 'display_name' => 'Pending Drivers'],
        ['name' => 'driver.document.list', 'group' => 'drivers-document', 'display_name' => 'List Driver Documents'],
        ['name' => 'driver.document.edit', 'group' => 'drivers-document', 'display_name' => 'Edit Driver Documents'],

        // ── Items / Products ───────────────────────────────────────────────────
        ['name' => 'items',        'group' => 'items', 'display_name' => 'View Items'],
        ['name' => 'items.create', 'group' => 'items', 'display_name' => 'Create Items'],
        ['name' => 'items.edit',   'group' => 'items', 'display_name' => 'Edit Items'],

        // ── Item Attributes ────────────────────────────────────────────────────
        ['name' => 'attributes',        'group' => 'item-attribute', 'display_name' => 'View Attributes'],
        ['name' => 'attributes.create', 'group' => 'item-attribute', 'display_name' => 'Create Attributes'],
        ['name' => 'attributes.edit',   'group' => 'item-attribute', 'display_name' => 'Edit Attributes'],

        // ── Categories ─────────────────────────────────────────────────────────
        ['name' => 'categories',        'group' => 'category', 'display_name' => 'View Categories'],
        ['name' => 'categories.create', 'group' => 'category', 'display_name' => 'Create Categories'],
        ['name' => 'categories.edit',   'group' => 'category', 'display_name' => 'Edit Categories'],

        // ── Orders ─────────────────────────────────────────────────────────────
        ['name' => 'orders',              'group' => 'orders',       'display_name' => 'View Orders'],
        ['name' => 'orders.edit',         'group' => 'orders',       'display_name' => 'Edit Orders'],
        ['name' => 'vendors.orderprint',  'group' => 'orders',       'display_name' => 'Print Orders'],
        ['name' => 'stores.booktable',    'group' => 'dinein-orders', 'display_name' => 'View Dine-In Orders'],
        ['name' => 'booktable.edit',      'group' => 'dinein-orders', 'display_name' => 'Edit Dine-In Orders'],

        // ── Coupons ────────────────────────────────────────────────────────────
        ['name' => 'coupons',        'group' => 'coupons', 'display_name' => 'View Coupons'],
        ['name' => 'coupons.create', 'group' => 'coupons', 'display_name' => 'Create Coupons'],
        ['name' => 'coupons.edit',   'group' => 'coupons', 'display_name' => 'Edit Coupons'],

        // ── Payments & Payouts ─────────────────────────────────────────────────
        ['name' => 'payment-method',         'group' => 'payment-method',  'display_name' => 'Manage Payment Methods'],
        ['name' => 'payments',               'group' => 'payments',         'display_name' => 'View Payments'],
        ['name' => 'payoutRequests.stores',  'group' => 'payout-request',   'display_name' => 'Store Payout Requests'],
        ['name' => 'payoutRequests.drivers', 'group' => 'payout-request',   'display_name' => 'Driver Payout Requests'],
        ['name' => 'driver.driverpayments',  'group' => 'driver-payments',  'display_name' => 'View Driver Payments'],
        ['name' => 'driversPayouts',         'group' => 'driver-payouts',   'display_name' => 'View Driver Payouts'],
        ['name' => 'driversPayouts.create',  'group' => 'driver-payouts',   'display_name' => 'Create Driver Payouts'],
        ['name' => 'storesPayouts',          'group' => 'store-payouts',    'display_name' => 'View Store Payouts'],
        ['name' => 'storesPayouts.create',   'group' => 'store-payouts',    'display_name' => 'Create Store Payouts'],
        ['name' => 'walletstransaction',     'group' => 'wallet-transaction', 'display_name' => 'Wallet Transactions'],

        // ── Reports ────────────────────────────────────────────────────────────
        ['name' => 'report.index', 'group' => 'reports', 'display_name' => 'View Reports'],

        // ── CMS / Banners ──────────────────────────────────────────────────────
        ['name' => 'setting.banners',        'group' => 'banners', 'display_name' => 'View Banners'],
        ['name' => 'setting.banners.create', 'group' => 'banners', 'display_name' => 'Create Banners'],
        ['name' => 'setting.banners.edit',   'group' => 'banners', 'display_name' => 'Edit Banners'],
        ['name' => 'cms',        'group' => 'cms', 'display_name' => 'View CMS Pages'],
        ['name' => 'cms.create', 'group' => 'cms', 'display_name' => 'Create CMS Pages'],
        ['name' => 'cms.edit',   'group' => 'cms', 'display_name' => 'Edit CMS Pages'],

        // ── Documents ─────────────────────────────────────────────────────────
        ['name' => 'documents.list',   'group' => 'documents', 'display_name' => 'View Documents'],
        ['name' => 'documents.create', 'group' => 'documents', 'display_name' => 'Create Documents'],
        ['name' => 'documents.edit',   'group' => 'documents', 'display_name' => 'Edit Documents'],

        // ── Email Templates ────────────────────────────────────────────────────
        ['name' => 'email-templates.index',  'group' => 'email-template', 'display_name' => 'View Email Templates'],
        ['name' => 'email-templates.edit',   'group' => 'email-template', 'display_name' => 'Edit Email Templates'],
        ['name' => 'email-templates.delete', 'group' => 'email-template', 'display_name' => 'Delete Email Templates'],

        // ── Notifications ──────────────────────────────────────────────────────
        ['name' => 'dynamic-notification.index',  'group' => 'dynamic-notifications', 'display_name' => 'View Dynamic Notifications'],
        ['name' => 'dynamic-notification.save',   'group' => 'dynamic-notifications', 'display_name' => 'Create Dynamic Notifications'],
        ['name' => 'dynamic-notification.delete', 'group' => 'dynamic-notifications', 'display_name' => 'Delete Dynamic Notifications'],
        ['name' => 'notification',      'group' => 'general-notifications', 'display_name' => 'View Notifications'],
        ['name' => 'notification.send', 'group' => 'general-notifications', 'display_name' => 'Send Notifications'],

        // ── Gift Cards ─────────────────────────────────────────────────────────
        ['name' => 'gift-card.index', 'group' => 'gift-cards', 'display_name' => 'View Gift Cards'],
        ['name' => 'gift-card.save',  'group' => 'gift-cards', 'display_name' => 'Create Gift Cards'],
        ['name' => 'gift-card.edit',  'group' => 'gift-cards', 'display_name' => 'Edit Gift Cards'],

        // ── Settings ───────────────────────────────────────────────────────────
        ['name' => 'settings.app.languages',        'group' => 'language',  'display_name' => 'View Languages'],
        ['name' => 'settings.app.languages.create', 'group' => 'language',  'display_name' => 'Create Languages'],
        ['name' => 'settings.app.languages.edit',   'group' => 'language',  'display_name' => 'Edit Languages'],
        ['name' => 'currencies',                     'group' => 'currency',  'display_name' => 'View Currencies'],
        ['name' => 'currencies.create',              'group' => 'currency',  'display_name' => 'Create Currencies'],
        ['name' => 'currencies.edit',                'group' => 'currency',  'display_name' => 'Edit Currencies'],
        ['name' => 'tax',                            'group' => 'tax',       'display_name' => 'View Tax'],
        ['name' => 'tax.create',                     'group' => 'tax',       'display_name' => 'Create Tax'],
        ['name' => 'tax.edit',                       'group' => 'tax',       'display_name' => 'Edit Tax'],
        ['name' => 'zone.list',                      'group' => 'zone',      'display_name' => 'View Zones'],
        ['name' => 'zone.create',                    'group' => 'zone',      'display_name' => 'Create Zones'],
        ['name' => 'zone.edit',                      'group' => 'zone',      'display_name' => 'Edit Zones'],
        ['name' => 'settings.app.globals',           'group' => 'global-setting',         'display_name' => 'Global Settings'],
        ['name' => 'settings.app.deliveryCharge',    'group' => 'delivery-charge',        'display_name' => 'Delivery Charge Settings'],
        ['name' => 'settings.app.radiusConfiguration','group' => 'radius',               'display_name' => 'Radius Configuration'],
        ['name' => 'settings.app.adminCommission',   'group' => 'admin-commission',       'display_name' => 'Admin Commission Settings'],
        ['name' => 'settings.app.bookTable',         'group' => 'dinein',                 'display_name' => 'Dine-In / Book Table Settings'],
        ['name' => 'settings.app.documentVerification','group' => 'document-verification','display_name' => 'Document Verification Settings'],
        ['name' => 'setting.specialOffer',           'group' => 'special-offer',          'display_name' => 'Special Offer Settings'],
        ['name' => 'privacyPolicy',                  'group' => 'privacy',                'display_name' => 'Privacy Policy'],
        ['name' => 'termsAndConditions',             'group' => 'terms',                  'display_name' => 'Terms & Conditions'],

        // ── Map / God Eye ──────────────────────────────────────────────────────
        ['name' => 'map', 'group' => 'god-eye', 'display_name' => 'God-Eye Map'],

        // ── Review Attributes ──────────────────────────────────────────────────
        ['name' => 'reviewattributes',        'group' => 'review-attribute', 'display_name' => 'View Review Attributes'],
        ['name' => 'reviewattributes.create', 'group' => 'review-attribute', 'display_name' => 'Create Review Attributes'],
        ['name' => 'reviewattributes.edit',   'group' => 'review-attribute', 'display_name' => 'Edit Review Attributes'],

        // ── RBAC / Roles ───────────────────────────────────────────────────────
        ['name' => 'role.index',         'group' => 'roles', 'display_name' => 'View Roles'],
        ['name' => 'role.save',          'group' => 'roles', 'display_name' => 'Create Role Form'],
        ['name' => 'role.store',         'group' => 'roles', 'display_name' => 'Create Roles'],
        ['name' => 'role.edit',          'group' => 'roles', 'display_name' => 'Edit Roles'],
        ['name' => 'role.update',        'group' => 'roles', 'display_name' => 'Update Roles'],
        ['name' => 'role.delete',        'group' => 'roles', 'display_name' => 'Delete Roles'],
        ['name' => 'permissions.manage', 'group' => 'roles', 'display_name' => 'Manage Permissions'],
    ];

    /**
     * Default roles with their permission groups.
     */
    private array $roles = [
        [
            'role_name'    => 'Super Admin',
            'slug'         => 'super-admin',
            'guard'        => 'admin',
            'is_active'    => true,
            'perm_groups'  => [],   // all-permissions — handled separately below
        ],
        [
            'role_name'    => 'Store Manager',
            'slug'         => 'store-manager',
            'guard'        => 'admin',
            'is_active'    => true,
            'perm_groups'  => [
                'stores', 'items', 'item-attribute', 'category', 'orders', 'dinein-orders',
                'coupons', 'reports', 'banners', 'vendors', 'vendors-document',
                'approve_vendors', 'pending_vendors',
            ],
        ],
        [
            'role_name'    => 'Finance Manager',
            'slug'         => 'finance-manager',
            'guard'        => 'admin',
            'is_active'    => true,
            'perm_groups'  => [
                'payments', 'payout-request', 'driver-payments', 'driver-payouts',
                'store-payouts', 'wallet-transaction', 'reports', 'gift-cards',
            ],
        ],
        [
            'role_name'    => 'Content Manager',
            'slug'         => 'content-manager',
            'guard'        => 'admin',
            'is_active'    => true,
            'perm_groups'  => [
                'banners', 'cms', 'email-template', 'dynamic-notifications',
                'general-notifications', 'special-offer', 'privacy', 'terms',
            ],
        ],
        [
            'role_name'    => 'Driver Manager',
            'slug'         => 'driver-manager',
            'guard'        => 'admin',
            'is_active'    => true,
            'perm_groups'  => [
                'drivers', 'approve_drivers', 'pending_drivers', 'drivers-document',
                'driver-payments', 'driver-payouts',
            ],
        ],
        [
            'role_name'    => 'Support Staff',
            'slug'         => 'support-staff',
            'guard'        => 'admin',
            'is_active'    => true,
            'perm_groups'  => ['users', 'orders', 'reports'],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function () {

            // ── 1. Seed permissions (upsert by slug) ──────────────────────────
            foreach ($this->permissions as $perm) {
                Permission::firstOrCreate(
                    ['slug' => $perm['name']],
                    ['name' => $perm['name'], 'group' => $perm['group'], 'display_name' => $perm['display_name']]
                );
            }

            $this->command->info('✓ Seeded ' . count($this->permissions) . ' permissions.');

            // ── 2. Seed default roles and sync their permissions ───────────────
            foreach ($this->roles as $roleData) {
                $permGroups = $roleData['perm_groups'];
                unset($roleData['perm_groups']);

                /** @var Role $role */
                $role = Role::firstOrCreate(
                    ['slug' => $roleData['slug']],
                    $roleData
                );

                // Super Admin gets ALL permissions
                if ($role->slug === 'super-admin') {
                    $ids = Permission::pluck('id')->toArray();
                } else {
                    $ids = Permission::whereIn('group', $permGroups)->pluck('id')->toArray();
                }
                $role->permissions()->sync($ids);

                $this->command->info("  ✓ Role '{$role->role_name}' → " . count($ids) . " permissions.");
            }

            $this->command->info('✓ Seeded ' . count($this->roles) . ' default roles.');
        });
    }
}
