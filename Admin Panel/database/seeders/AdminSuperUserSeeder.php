<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * AdminSuperUserSeeder
 *
 * Creates the Super Admin user and 4 staff admins.
 * Must run AFTER AdminRbacSeeder (needs roles table populated).
 *
 * Super Admin Credentials:
 *   Email   : admin@plantix.com
 *   Password: Admin@123456
 */
class AdminSuperUserSeeder extends Seeder
{
    public function run(): void
    {
        $now          = Carbon::now();
        $superRoleId  = DB::table('roles')->where('slug', 'super-admin')->value('id');

        // ── Super Admin (no role_id needed — role=admin with null role_id = unrestricted)
        // We also assign role_id for completeness
        DB::table('users')->insert([
            'name'               => 'Super Admin',
            'email'              => 'admin@plantix.com',
            'password'           => Hash::make('Admin@123456'),
            'phone'              => '+923001000001',
            'role'               => 'admin',
            'role_id'            => $superRoleId,
            'active'             => 1,
            'email_verified_at'  => $now,
            'password_changed_at'=> $now,
            'created_at'         => $now->copy()->subDays(180),
            'updated_at'         => $now,
        ]);

        // ── Staff Admins ──────────────────────────────────────────────────────
        $staff = [
            ['name' => 'Bilal Chaudhry',  'email' => 'store@plantix.com',   'slug' => 'store-manager',    'phone' => '+923011100001'],
            ['name' => 'Sara Ahmed',       'email' => 'orders@plantix.com',  'slug' => 'order-manager',    'phone' => '+923011100002'],
            ['name' => 'Nadia Khan',       'email' => 'content@plantix.com', 'slug' => 'content-moderator','phone' => '+923011100003'],
            ['name' => 'Faisal Iqbal',     'email' => 'finance@plantix.com', 'slug' => 'finance-manager',  'phone' => '+923011100004'],
        ];

        foreach ($staff as $s) {
            $rid = DB::table('roles')->where('slug', $s['slug'])->value('id');
            DB::table('users')->insert([
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
            ]);
        }

        if ($this->command) {
            $this->command->info('');
            $this->command->info('  ╔══════════════════════════════════════════╗');
            $this->command->info('  ║         SUPER ADMIN CREDENTIALS         ║');
            $this->command->info('  ╠══════════════════════════════════════════╣');
            $this->command->info('  ║  Email   : admin@plantix.com            ║');
            $this->command->info('  ║  Password: Admin@123456                 ║');
            $this->command->info('  ╚══════════════════════════════════════════╝');
            $this->command->info('');
        }
    }
}
