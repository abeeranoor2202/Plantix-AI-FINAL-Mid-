<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenerateProductSkusSeeder extends Seeder
{
    public function run(): void
    {
        $products = DB::table('products')->orderBy('id')->get(['id', 'name', 'sku']);

        $updated = 0;
        foreach ($products as $p) {
            if (! empty($p->sku)) {
                continue; // already has a SKU
            }

            $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $p->name));
            $sku  = substr($base, 0, 6) . '-' . str_pad($p->id, 4, '0', STR_PAD_LEFT);

            DB::table('products')->where('id', $p->id)->update(['sku' => $sku]);
            $updated++;
        }

        $this->command->info("Generated SKUs for {$updated} products.");
    }
}
