<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductStockSeeder extends Seeder
{
    public function run(): void
    {
        $now      = Carbon::now();
        $products = DB::table('products')->select('id', 'vendor_id', 'stock_quantity', 'name')->get();

        foreach ($products as $p) {
            $qty = $p->stock_quantity ?: rand(10, 500);

            DB::table('product_stocks')->insert([
                'product_id'         => $p->id,
                'vendor_id'          => $p->vendor_id,
                'quantity'           => $qty,
                'low_stock_threshold' => 5,
                'sku'                => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $p->name), 0, 6)) . '-' . str_pad($p->id, 4, '0', STR_PAD_LEFT),
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // Add primary images for all products
        foreach ($products as $p) {
            DB::table('product_images')->insert([
                'product_id' => $p->id,
                'path'       => 'products/placeholder-' . $p->id . '.jpg',
                'alt_text'   => $p->name,
                'sort_order' => 1,
                'is_primary' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
