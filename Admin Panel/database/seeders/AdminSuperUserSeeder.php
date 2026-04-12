<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * AdminSuperUserSeeder — Plantix AI Admin Panel
 *
 * Creates the Super Admin and four role-specific staff accounts.
 * Must run AFTER AdminRbacSeeder (needs the roles table populated).
 *
 * ┌─────────────────────┬────────────────────────────┬───────────────────────────┬──────────────────────────────┐
 * │ Name                │ Email                      │ Password                  │ Role                         │
 * ├─────────────────────┼────────────────────────────┼───────────────────────────┼──────────────────────────────┤
 * │ Super Admin         │ admin@gmail.com            │ 12345678              │ (null role_id — unrestricted)│
 * │ Bilal Chaudhry      │ catalog@plantix.com        │ Staff@123456              │ Catalog Manager              │
 * │ Sara Ahmed          │ users@plantix.com          │ Staff@123456              │ User & Vendor Manager        │
 * │ Nadia Khan          │ comms@plantix.com          │ Staff@123456              │ Communications Manager       │
 * │ Faisal Iqbal        │ sysadmin@plantix.com       │ Staff@123456              │ System Admin                 │
 * └─────────────────────┴────────────────────────────┴───────────────────────────┴──────────────────────────────┘
 */
class AdminSuperUserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // ── Super Admin ───────────────────────────────────────────────────────
        // role_id = NULL → bypasses ALL permission middleware (unrestricted access).
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@gmail.com'],
            [
                'name'                => 'Super Admin',
                'email'               => 'admin@gmail.com',
                'password'            => Hash::make('12345678'),
                'phone'               => '+923001000001',
                'role'                => 'admin',
                'role_id'             => null,
                'active'              => 1,
                'email_verified_at'   => $now,
                'password_changed_at' => $now,
                'created_at'          => $now->copy()->subDays(180),
                'updated_at'          => $now,
            ]
        );

        // ── Staff Admins ──────────────────────────────────────────────────────
        $staff = [
            [
                'name'  => 'Bilal Chaudhry',
                'email' => 'catalog@plantix.com',
                'slug'  => 'catalog-manager',
                'phone' => '+923011100001',
            ],
            [
                'name'  => 'Sara Ahmed',
                'email' => 'users@plantix.com',
                'slug'  => 'user-vendor-manager',
                'phone' => '+923011100002',
            ],
            [
                'name'  => 'Nadia Khan',
                'email' => 'comms@plantix.com',
                'slug'  => 'communications-manager',
                'phone' => '+923011100003',
            ],
            [
                'name'  => 'Faisal Iqbal',
                'email' => 'sysadmin@plantix.com',
                'slug'  => 'system-admin',
                'phone' => '+923011100004',
            ],
        ];

        foreach ($staff as $s) {
            $rid = DB::table('roles')->where('slug', $s['slug'])->value('id');
            DB::table('users')->updateOrInsert(
                ['email' => $s['email']],
                [
                    'name'              => $s['name'],
                    'email'             => $s['email'],
                    'password'          => Hash::make('Staff@123456'),
                    'phone'             => $s['phone'],
                    'role'              => 'admin',
                    'role_id'           => $rid,
                    'active'            => 1,
                    'email_verified_at' => $now,
                    'created_at'        => $now->copy()->subDays(rand(30, 150)),
                    'updated_at'        => $now,
                ]
            );
        }

        if ($this->command) {
            $this->command->info('');
            $this->command->info('  ╔══════════════════════════════════════════════════════════╗');
            $this->command->info('  ║              PLANTIX AI — ADMIN CREDENTIALS             ║');
            $this->command->info('  ╠══════════════════════════════════════════════════════════╣');
            $this->command->info('  ║  SUPER ADMIN (unrestricted)                             ║');
            $this->command->info('  ║    Email   : admin@plantix.com                          ║');
            $this->command->info('  ║    Password: Admin@123456                               ║');
            $this->command->info('  ╠══════════════════════════════════════════════════════════╣');
            $this->command->info('  ║  STAFF ACCOUNTS  (password for all: Staff@123456)       ║');
            $this->command->info('  ║    catalog@plantix.com   → Catalog Manager              ║');
            $this->command->info('  ║    users@plantix.com     → User & Vendor Manager        ║');
            $this->command->info('  ║    comms@plantix.com     → Communications Manager       ║');
            $this->command->info('  ║    sysadmin@plantix.com  → System Admin                 ║');
            $this->command->info('  ╚══════════════════════════════════════════════════════════╝');
            $this->command->info('');
        }
    }
}
