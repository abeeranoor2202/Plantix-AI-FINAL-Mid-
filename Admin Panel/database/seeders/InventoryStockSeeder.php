<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * InventoryStockSeeder
 *
 * Creates a Stock record for every product that doesn't already have one,
 * then seeds a handful of StockMovement history rows per product so the
 * "Recent Stock Movements" table on the vendor inventory page has data.
 *
 * Safe to re-run — uses updateOrCreate so existing records are not duplicated.
 */
class InventoryStockSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $products = Product::all();

        if ($products->isEmpty()) {
            $this->command->warn('No products found — run ProductsSeeder first.');
            return;
        }

        $this->command->info("Seeding stocks for {$products->count()} products...");

        foreach ($products as $product) {
            $qty       = $this->randomQty($product->name);
            $threshold = $this->randomThreshold($qty);
            $status    = $qty <= 0 ? 'out_of_stock' : 'in_stock';

            $stock = Stock::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'vendor_id'  => $product->vendor_id,
                ],
                [
                    'quantity'            => $qty,
                    'reserved_quantity'   => $this->randomReserved($qty),
                    'low_stock_threshold' => $threshold,
                    'status'              => $status,
                    'is_available'        => true,
                ]
            );

            // Seed 3–6 movement history rows per product
            $this->seedMovements($stock, $product->vendor_id, $now);
        }

        $this->command->info('Done. Stocks: ' . Stock::count() . ', Movements: ' . StockMovement::count());
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function randomQty(string $name): int
    {
        // Give realistic quantities based on product category keywords
        $name = strtolower($name);

        if (str_contains($name, 'seed') || str_contains($name, 'combo')) {
            return rand(100, 600);
        }
        if (str_contains($name, 'urea') || str_contains($name, 'dap') || str_contains($name, 'npk')) {
            return rand(50, 300);
        }
        if (str_contains($name, 'tool') || str_contains($name, 'spade') || str_contains($name, 'hoe') || str_contains($name, 'shear') || str_contains($name, 'trowel') || str_contains($name, 'shovel')) {
            return rand(10, 80);
        }
        if (str_contains($name, 'pesticide') || str_contains($name, 'insecticide') || str_contains($name, 'fungicide') || str_contains($name, 'herbicide') || str_contains($name, 'glyphosate') || str_contains($name, 'mancozeb') || str_contains($name, 'chlorpyrifos') || str_contains($name, 'imidacloprid') || str_contains($name, 'copper') || str_contains($name, 'metaldehyde') || str_contains($name, 'weed') || str_contains($name, 'keera')) {
            return rand(80, 350);
        }

        return rand(20, 200);
    }

    private function randomThreshold(int $qty): int
    {
        // Low-stock threshold is roughly 10–20% of initial quantity, min 5
        return max(5, (int) round($qty * (rand(10, 20) / 100)));
    }

    private function randomReserved(int $qty): int
    {
        if ($qty <= 0) {
            return 0;
        }
        // 0–15% of qty reserved for pending orders
        return min($qty, (int) round($qty * (rand(0, 15) / 100)));
    }

    private function seedMovements(Stock $stock, int $vendorId, Carbon $now): void
    {
        $types = ['in', 'out', 'reserved', 'released'];
        $refs  = ['manual_restock', 'order_fulfillment', 'order_cancellation', 'inventory_count', 'supplier_delivery', 'return_received'];

        $count = rand(3, 6);
        for ($i = 0; $i < $count; $i++) {
            $type = $types[array_rand($types)];
            $qty  = match ($type) {
                'in'       => rand(10, 100),
                'out'      => rand(1, 20),
                'reserved' => rand(1, 10),
                'released' => rand(1, 5),
            };

            StockMovement::create([
                'product_id' => $stock->product_id,
                'vendor_id'  => $vendorId,
                'type'       => $type,
                'quantity'   => $qty,
                'reference'  => $refs[array_rand($refs)],
                'created_at' => $now->copy()->subDays(rand(1, 60))->subHours(rand(0, 23)),
            ]);
        }
    }
}
