<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * UsersSeeder
 *
 * Inserts:
 *   - 5  vendor owner accounts  (role = vendor)
 *   - 5  expert accounts        (role = expert)
 *   - 50 customer accounts      (role = user)
 *   - Edge cases: 2 inactive, 1 banned, 1 shadow-banned
 */
class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $pw  = Hash::make('User@123456');

        // ── Vendor users ──────────────────────────────────────────────────────
        $vendors = [
            ['name' => 'Khalid Mahmood',  'email' => 'khalid@greenharvest.pk',   'phone' => '+923021200001'],
            ['name' => 'Asma Siddiqui',   'email' => 'asma@pakiagropro.pk',       'phone' => '+923021200002'],
            ['name' => 'Tariq Hussain',   'email' => 'tariq@farmtech.pk',          'phone' => '+923021200003'],
            ['name' => 'Rukhsana Bibi',   'email' => 'rukhsana@kisanmart.pk',      'phone' => '+923021200004'],
            ['name' => 'Imran Farooq',    'email' => 'imran@agrishield.pk',        'phone' => '+923021200005'],
            ['name' => 'Zainab Malik',    'email' => 'zainab@naturecrop.pk',       'phone' => '+923021200006'],
        ];
        foreach ($vendors as $v) {
            DB::table('users')->insert(array_merge($v, [
                'password' => $pw, 'role' => 'vendor', 'active' => 1,
                'email_verified_at' => $now,
                'created_at' => $now->copy()->subDays(rand(60, 300)),
                'updated_at' => $now,
            ]));
        }

        // ── Expert users ──────────────────────────────────────────────────────
        $experts = [
            ['name' => 'Dr. Ahmed Raza',    'email' => 'ahmed.raza@plantix.com',   'phone' => '+923031300001'],
            ['name' => 'Prof. Amina Malik', 'email' => 'amina.malik@plantix.com',  'phone' => '+923031300002'],
            ['name' => 'Engr. Usman Tariq', 'email' => 'usman.tariq@plantix.com',  'phone' => '+923031300003'],
            ['name' => 'Dr. Fatima Nawaz',  'email' => 'fatima.nawaz@plantix.com', 'phone' => '+923031300004'],
            ['name' => 'Dr. Hassan Iqbal',  'email' => 'hassan.iqbal@plantix.com', 'phone' => '+923031300005'],
        ];
        foreach ($experts as $e) {
            DB::table('users')->insert(array_merge($e, [
                'password' => $pw, 'role' => 'expert', 'active' => 1,
                'email_verified_at' => $now,
                'created_at' => $now->copy()->subDays(rand(30, 180)),
                'updated_at' => $now,
            ]));
        }

        // ── Customer users ────────────────────────────────────────────────────
        $customers = [
            ['name' => 'Muhammad Ali',      'email' => 'mali@email.com',        'phone' => '+923041400001'],
            ['name' => 'Fatimah Zahra',     'email' => 'fzahra@email.com',      'phone' => '+923041400002'],
            ['name' => 'Hamza Sheikh',      'email' => 'hsheikh@email.com',     'phone' => '+923041400003'],
            ['name' => 'Ayesha Noor',       'email' => 'anoor@email.com',       'phone' => '+923041400004'],
            ['name' => 'Bilal Aslam',       'email' => 'baslam@email.com',      'phone' => '+923041400005'],
            ['name' => 'Saima Baig',        'email' => 'sbaig@email.com',       'phone' => '+923041400006'],
            ['name' => 'Usman Ghani',       'email' => 'ughani@email.com',      'phone' => '+923041400007'],
            ['name' => 'Rabia Yousuf',      'email' => 'ryousuf@email.com',     'phone' => '+923041400008'],
            ['name' => 'Talha Qureshi',     'email' => 'tqureshi@email.com',    'phone' => '+923041400009'],
            ['name' => 'Mariam Afzal',      'email' => 'mafzal@email.com',      'phone' => '+923041400010'],
            ['name' => 'Junaid Hassan',     'email' => 'jhassan@email.com',     'phone' => '+923041400011'],
            ['name' => 'Hina Latif',        'email' => 'hlatif@email.com',      'phone' => '+923041400012'],
            ['name' => 'Shahid Mehmood',    'email' => 'smehmood@email.com',    'phone' => '+923041400013'],
            ['name' => 'Nadia Perveen',     'email' => 'nperveen@email.com',    'phone' => '+923041400014'],
            ['name' => 'Fawad Chaudhry',    'email' => 'fchaudhry@email.com',   'phone' => '+923041400015'],
            ['name' => 'Zara Anjum',        'email' => 'zanjum@email.com',      'phone' => '+923041400016'],
            ['name' => 'Wasif Raza',        'email' => 'wraza@email.com',       'phone' => '+923041400017'],
            ['name' => 'Sana Iqbal',        'email' => 'siqbal@email.com',      'phone' => '+923041400018'],
            ['name' => 'Adeel Khan',        'email' => 'akhan@email.com',       'phone' => '+923041400019'],
            ['name' => 'Amna Khatoon',      'email' => 'akhatoon@email.com',    'phone' => '+923041400020'],
            ['name' => 'Zubair Ahmad',      'email' => 'zahmad@email.com',      'phone' => '+923041400021'],
            ['name' => 'Shazia Bibi',       'email' => 'sbibi@email.com',       'phone' => '+923041400022'],
            ['name' => 'Nasir Mahmood',     'email' => 'nmahmood@email.com',    'phone' => '+923041400023'],
            ['name' => 'Iram Shakeel',      'email' => 'ishakeel@email.com',    'phone' => '+923041400024'],
            ['name' => 'Aamir Sohail',      'email' => 'asohail@email.com',     'phone' => '+923041400025'],
            ['name' => 'Kiran Fayyaz',      'email' => 'kfayyaz@email.com',     'phone' => '+923041400026'],
            ['name' => 'Rizwan Malik',      'email' => 'rmalik@email.com',      'phone' => '+923041400027'],
            ['name' => 'Sobia Nisar',       'email' => 'snisar@email.com',      'phone' => '+923041400028'],
            ['name' => 'Danish Maqbool',    'email' => 'dmaqbool@email.com',    'phone' => '+923041400029'],
            ['name' => 'Tahira Rehman',     'email' => 'trehman@email.com',     'phone' => '+923041400030'],
            ['name' => 'Arif Butt',         'email' => 'abutt@email.com',       'phone' => '+923041400031'],
            ['name' => 'Samina Qazi',       'email' => 'sqazi@email.com',       'phone' => '+923041400032'],
            ['name' => 'Iqbal Hussain',     'email' => 'ihussain@email.com',    'phone' => '+923041400033'],
            ['name' => 'Lubna Arshad',      'email' => 'larshad@email.com',     'phone' => '+923041400034'],
            ['name' => 'Sajid Nawaz',       'email' => 'snawaz@email.com',      'phone' => '+923041400035'],
            ['name' => 'Farah Zafar',       'email' => 'fzafar@email.com',      'phone' => '+923041400036'],
            ['name' => 'Imtiaz Ahmad',      'email' => 'iahmad@email.com',      'phone' => '+923041400037'],
            ['name' => 'Naima Anwar',       'email' => 'nanwar@email.com',      'phone' => '+923041400038'],
            ['name' => 'Kamran Akhter',     'email' => 'kakhter@email.com',     'phone' => '+923041400039'],
            ['name' => 'Sabiha Memon',      'email' => 'smemon@email.com',      'phone' => '+923041400040'],
            ['name' => 'Pervaiz Alam',      'email' => 'palam@email.com',       'phone' => '+923041400041'],
            ['name' => 'Asifa Jabeen',      'email' => 'ajabeen@email.com',     'phone' => '+923041400042'],
            ['name' => 'Tariq Mehmood',     'email' => 'tmehmood@email.com',    'phone' => '+923041400043'],
            ['name' => 'Razia Sultana',     'email' => 'rsultana@email.com',    'phone' => '+923041400044'],
            ['name' => 'Shahzad Dar',       'email' => 'sdar@email.com',        'phone' => '+923041400045'],
            ['name' => 'Faiza Khurshid',    'email' => 'fkhurshid@email.com',   'phone' => '+923041400046'],
            ['name' => 'Naveed Akhtar',     'email' => 'nakhtar@email.com',     'phone' => '+923041400047'],
            ['name' => 'Mehnaz Shabbir',    'email' => 'mshabbir@email.com',    'phone' => '+923041400048'],
        ];

        foreach ($customers as $c) {
            DB::table('users')->insert(array_merge($c, [
                'password'          => $pw,
                'role'              => 'user',
                'active'            => 1,
                'email_verified_at' => $now,
                'wallet_amount'     => round(rand(0, 5000) / 100, 2),
                'created_at'        => $now->copy()->subDays(rand(1, 365)),
                'updated_at'        => $now,
            ]));
        }

        // ── Edge cases ────────────────────────────────────────────────────────

        // 2 inactive accounts
        foreach (['inactive1@email.com', 'inactive2@email.com'] as $idx => $em) {
            DB::table('users')->insert([
                'name' => 'Inactive User '.($idx + 1), 'email' => $em,
                'password' => $pw, 'role' => 'user', 'active' => 0,
                'email_verified_at' => $now,
                'created_at' => $now->copy()->subDays(rand(90, 200)),
                'updated_at' => $now,
            ]);
        }

        // 1 permanently banned account
        DB::table('users')->insert([
            'name' => 'Banned User', 'email' => 'banned@email.com',
            'password' => $pw, 'role' => 'user', 'active' => 1,
            'email_verified_at'  => $now,
            'is_banned'          => 1,
            'banned_reason'      => 'Multiple fraud orders detected',
            'banned_until'       => null, // permanent
            'is_shadow_banned'   => 0,
            'created_at'         => $now->copy()->subDays(200),
            'updated_at'         => $now,
        ]);

        // 1 shadow-banned account
        DB::table('users')->insert([
            'name' => 'Shadow User', 'email' => 'shadow@email.com',
            'password' => $pw, 'role' => 'user', 'active' => 1,
            'email_verified_at' => $now,
            'is_shadow_banned'  => 1,
            'created_at'        => $now->copy()->subDays(150),
            'updated_at'        => $now,
        ]);

        // 1 unverified email
        DB::table('users')->insert([
            'name' => 'Unverified User', 'email' => 'unverified@email.com',
            'password' => $pw, 'role' => 'user', 'active' => 1,
            'email_verified_at' => null,
            'created_at'        => $now->copy()->subDays(3),
            'updated_at'        => $now,
        ]);
    }
}
