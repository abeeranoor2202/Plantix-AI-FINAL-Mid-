<?php
/**
 * fix_admin_role.php — Plantix AI Admin Panel
 *
 * Ensures ONLY the Super Admin (admin@plantix.com) has role_id = NULL.
 * Staff admins retain their assigned role_id.
 * Run once via:  php fix_admin_role.php
 */
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Fix Super Admin — must have NULL role_id to bypass permission middleware
    $superAdmin = \App\Models\User::where('email', 'admin@plantix.com')->first();
    if ($superAdmin) {
        $superAdmin->update(['role_id' => null]);
        echo "✓ Super Admin (admin@plantix.com): role_id set to NULL (unrestricted).\n";
    } else {
        echo "⚠ Super Admin not found — run DatabaseSeeder first.\n";
    }

    // Report on all admin users
    echo "\nAll admin accounts:\n";
    \App\Models\User::where('role', 'admin')->get()->each(function ($u) {
        $roleName = $u->role_id
            ? (\App\Models\Role::find($u->role_id)?->role_name ?? 'Unknown role')
            : '(Super Admin — unrestricted)';
        echo sprintf(
            "  %-28s role_id=%-5s  %s\n",
            $u->email,
            $u->role_id ?? 'null',
            $roleName
        );
    });
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

