<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * AdminRbacSeeder — Plantix AI Admin Panel
 *
 * Permissions are derived EXCLUSIVELY from actual `permission:` middleware
 * usage in routes/panels/admin.php.  Any legacy slugs that had no matching
 * route middleware have been removed to keep the permission table clean.
 *
 * Roles & their groups:
 * ┌──────────────────────────┬──────────────────────────────────────────────────────────────────┐
 * │ Role                     │ Permission groups                                                │
 * ├──────────────────────────┼──────────────────────────────────────────────────────────────────┤
 * │ Super Admin (null rId)   │ ALL — also bypasses middleware via null role_id                  │
 * │ Catalog Manager          │ category, item-attribute, coupons, reviews, vendors              │
 * │ User & Vendor Manager    │ users, vendors                                                   │
 * │ Communications Manager   │ general-notifications, email-template                            │
 * │ System Admin             │ admins, roles, global-setting, payment-method                    │
 * └──────────────────────────┴──────────────────────────────────────────────────────────────────┘
 */
class AdminRbacSeeder extends Seeder
{
    /**
     * ONLY permissions that are actually enforced by permission middleware
     * in routes/panels/admin.php (39 total across 12 groups).
     */
    private array $permissions = [

        // ── Customer Users (group: users) ─────────────────────────────────────
        ['name' => 'users',        'group' => 'users', 'display_name' => 'View Customers'],
        ['name' => 'users.edit',   'group' => 'users', 'display_name' => 'Edit Customer'],
        ['name' => 'users.create', 'group' => 'users', 'display_name' => 'Create Customer'],
        ['name' => 'users.view',   'group' => 'users', 'display_name' => 'View Customer Detail'],

        // ── Admin Sub-users (group: admins) ───────────────────────────────────
        ['name' => 'admin.users',        'group' => 'admins', 'display_name' => 'View Admin Users'],
        ['name' => 'admin.users.create', 'group' => 'admins', 'display_name' => 'Create Admin User Form'],
        ['name' => 'admin.users.store',  'group' => 'admins', 'display_name' => 'Store Admin User'],
        ['name' => 'admin.users.edit',   'group' => 'admins', 'display_name' => 'Edit Admin User'],
        ['name' => 'admin.users.update', 'group' => 'admins', 'display_name' => 'Update Admin User'],
        ['name' => 'admin.users.delete', 'group' => 'admins', 'display_name' => 'Delete Admin User'],

        // ── Vendors (group: vendors) ──────────────────────────────────────────
        ['name' => 'vendors',         'group' => 'vendors', 'display_name' => 'View Vendors'],
        ['name' => 'vendors.view',    'group' => 'vendors', 'display_name' => 'View Vendor Detail'],
        ['name' => 'vendors.edit',    'group' => 'vendors', 'display_name' => 'Edit Vendor'],
        ['name' => 'vendors.toggle',  'group' => 'vendors', 'display_name' => 'Toggle Vendor Status'],

        // ── Categories (group: category) ──────────────────────────────────────
        ['name' => 'categories',        'group' => 'category', 'display_name' => 'View Categories'],
        ['name' => 'categories.create', 'group' => 'category', 'display_name' => 'Create Category'],
        ['name' => 'categories.edit',   'group' => 'category', 'display_name' => 'Edit Category'],

        // ── Product Attributes (group: item-attribute) ────────────────────────
        ['name' => 'attributes',        'group' => 'item-attribute', 'display_name' => 'View Attributes'],
        ['name' => 'attributes.create', 'group' => 'item-attribute', 'display_name' => 'Create Attribute'],
        ['name' => 'attributes.edit',   'group' => 'item-attribute', 'display_name' => 'Edit Attribute'],

        // ── Coupons / Discounts (group: coupons) ──────────────────────────────
        ['name' => 'coupons',        'group' => 'coupons', 'display_name' => 'View Coupons'],
        ['name' => 'coupons.create', 'group' => 'coupons', 'display_name' => 'Create Coupon'],
        ['name' => 'coupons.edit',   'group' => 'coupons', 'display_name' => 'Edit Coupon'],

        // ── Product Reviews (group: reviews) ──────────────────────────────────
        ['name' => 'reviews',        'group' => 'reviews', 'display_name' => 'View Reviews'],
        ['name' => 'reviews.delete', 'group' => 'reviews', 'display_name' => 'Delete Review'],

        // ── Roles & Permissions / RBAC (group: roles) ─────────────────────────
        ['name' => 'role.index',         'group' => 'roles', 'display_name' => 'View Roles'],
        ['name' => 'role.save',          'group' => 'roles', 'display_name' => 'Create Role Form'],
        ['name' => 'role.store',         'group' => 'roles', 'display_name' => 'Store Role'],
        ['name' => 'role.edit',          'group' => 'roles', 'display_name' => 'Edit Role'],
        ['name' => 'role.update',        'group' => 'roles', 'display_name' => 'Update Role'],
        ['name' => 'role.delete',        'group' => 'roles', 'display_name' => 'Delete Role'],
        ['name' => 'permissions.manage', 'group' => 'roles', 'display_name' => 'Manage Permissions'],

        // ── Global Settings (group: global-setting) ───────────────────────────
        ['name' => 'settings.app.globals', 'group' => 'global-setting', 'display_name' => 'Global App Settings'],

        // ── Payment Configuration (group: payment-method) ─────────────────────
        ['name' => 'payment-method', 'group' => 'payment-method', 'display_name' => 'Manage Payment Methods'],

        // ── Email Templates (group: email-template) ───────────────────────────
        ['name' => 'email-templates.index',  'group' => 'email-template', 'display_name' => 'View Email Templates'],
        ['name' => 'email-templates.edit',   'group' => 'email-template', 'display_name' => 'Edit Email Template'],
        ['name' => 'email-templates.delete', 'group' => 'email-template', 'display_name' => 'Delete Email Template'],

        // ── Notifications (group: general-notifications) ──────────────────────
        ['name' => 'notification',      'group' => 'general-notifications', 'display_name' => 'View Notifications'],
        ['name' => 'notification.send', 'group' => 'general-notifications', 'display_name' => 'Send Notification Broadcast'],
    ];

    /**
     * Roles and the permission GROUPS they receive.
     * Super Admin gets all permissions and also bypasses middleware via null role_id.
     */
    private array $roles = [
        [
            'role_name'   => 'Super Admin',
            'slug'        => 'super-admin',
            'description' => 'Unrestricted access — owns all permissions and bypasses RBAC middleware.',
            'guard'       => 'admin',
            'is_active'   => true,
            'perm_groups' => [],   // all-permissions handled separately
        ],
        [
            'role_name'   => 'Catalog Manager',
            'slug'        => 'catalog-manager',
            'description' => 'Manages product catalog: categories, attributes, coupons, reviews, and vendor listings.',
            'guard'       => 'admin',
            'is_active'   => true,
            'perm_groups' => ['category', 'item-attribute', 'coupons', 'reviews', 'vendors'],
        ],
        [
            'role_name'   => 'User & Vendor Manager',
            'slug'        => 'user-vendor-manager',
            'description' => 'Manages customer accounts and vendor onboarding.',
            'guard'       => 'admin',
            'is_active'   => true,
            'perm_groups' => ['users', 'vendors'],
        ],
        [
            'role_name'   => 'Communications Manager',
            'slug'        => 'communications-manager',
            'description' => 'Manages notification broadcasts and email templates.',
            'guard'       => 'admin',
            'is_active'   => true,
            'perm_groups' => ['general-notifications', 'email-template'],
        ],
        [
            'role_name'   => 'System Admin',
            'slug'        => 'system-admin',
            'description' => 'Manages admin sub-users, RBAC roles/permissions, global settings, and payment methods.',
            'guard'       => 'admin',
            'is_active'   => true,
            'perm_groups' => ['admins', 'roles', 'global-setting', 'payment-method'],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function () {

            // ── 1. Hard-delete any stale permissions no longer used in routes ──
            $activeslugs = array_column($this->permissions, 'name');
            $removed     = Permission::whereNotIn('name', $activeslugs)->delete();
            if ($removed) {
                $this->command->warn("  ✗ Removed {$removed} obsolete permission(s) that had no route middleware.");
            }

            // ── 2. Upsert current permission set ──────────────────────────────
            foreach ($this->permissions as $perm) {
                Permission::updateOrCreate(
                    ['name' => $perm['name']],
                    [
                        'slug'         => Str::slug($perm['name'], '.'),
                        'group'        => $perm['group'],
                        'display_name' => $perm['display_name'],
                    ]
                );
            }
            $this->command->info('✓ Upserted ' . count($this->permissions) . ' permissions.');

            // ── 3. Remove roles that no longer exist in the definition ─────────
            $activeSlugs = array_column($this->roles, 'slug');
            Role::whereNotIn('slug', $activeSlugs)->each(function (Role $r) {
                $r->permissions()->detach();
                $r->delete();
                $this->command->warn("  ✗ Removed obsolete role: {$r->role_name}");
            });

            // ── 4. Upsert roles and sync their permissions ─────────────────────
            foreach ($this->roles as $roleData) {
                $permGroups = $roleData['perm_groups'];
                unset($roleData['perm_groups']);

                /** @var Role $role */
                $role = Role::updateOrCreate(
                    ['slug' => $roleData['slug']],
                    $roleData
                );

                // Super Admin receives ALL permissions
                $ids = ($role->slug === 'super-admin')
                    ? Permission::pluck('id')->toArray()
                    : Permission::whereIn('group', $permGroups)->pluck('id')->toArray();

                $role->permissions()->sync($ids);

                $this->command->info("  ✓ Role '{$role->role_name}' → " . count($ids) . ' permission(s).');
            }

            $this->command->info('✓ RBAC seeding complete: ' . count($this->roles) . ' roles configured.');
        });
    }
}
