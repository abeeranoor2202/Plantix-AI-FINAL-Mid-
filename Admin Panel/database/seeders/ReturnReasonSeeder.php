<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReturnReasonSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $reasons = [
            'Product received was damaged',
            'Wrong item delivered',
            'Product differs from description or images',
            'Product quality is not as expected',
            'Expired or near-expiry product delivered',
            'Duplicate order placed by mistake',
            'Product did not work as intended',
            'Changed my mind after ordering',
            'Incomplete order — items missing',
            'Counterfeit or fake product received',
            'Allergic reaction or safety concern',
            'Better deal found elsewhere',
        ];

        foreach ($reasons as $reason) {
            DB::table('return_reasons')->insert([
                'reason'     => $reason,
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
