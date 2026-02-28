<?php

namespace App\Services\Shared;

use App\Models\Product;
use App\Models\ProductStock;
use App\Notifications\LowStockAlertNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * StockService
 *
 * Single responsibility: all stock mutation & validation logic lives here.
 * Controllers and the checkout service delegate to this class.
 */
class StockService
{
    /**
     * Assert that the product has enough stock for the requested quantity.
     * Throws a ValidationException if stock is insufficient.
     */
    public function assertSufficientStock(Product $product, int $requestedQty): void
    {
        if (! $product->track_stock) {
            return; // unlimited stock products
        }

        $available = $product->stock_quantity;

        if ($available < $requestedQty) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$available} unit(s) of \"{$product->name}\" are available.",
            ]);
        }
    }

    /**
     * Decrement stock after a confirmed sale.
     * Should always be called inside a DB::transaction().
     */
    public function decrementStock(Product $product, int $qty): void
    {
        if (! $product->track_stock) {
            return;
        }

        // Decrement on the products table (denormalised fast read)
        $product->decrement('stock_quantity', $qty);

        // Also decrement on product_stocks (vendor-level tracking)
        $stock = ProductStock::where('product_id', $product->id)
                             ->where('vendor_id', $product->vendor_id)
                             ->first();

        if ($stock) {
            $stock->decrement('quantity', $qty);
            $this->checkLowStockAlert($product, $stock->fresh());
        } else {
            // No ProductStock row — fall back to products.stock_quantity for alert
            $product->refresh();
            $threshold = (int) config('plantix.low_stock_threshold', 10);
            if ($product->stock_quantity >= 0 && $product->stock_quantity <= $threshold) {
                try {
                    User::where('role', 'admin')->get()
                        ->each(fn ($admin) => $admin->notify(
                            new LowStockAlertNotification($product, $product->stock_quantity)
                        ));
                } catch (\Throwable $e) {
                    Log::warning('Low-stock alert (products table) notification failed: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Restock a product (admin or vendor).
     */
    public function restock(Product $product, int $qty, int $vendorId): void
    {
        $product->increment('stock_quantity', $qty);

        ProductStock::updateOrCreate(
            ['product_id' => $product->id, 'vendor_id' => $vendorId],
            ['quantity'   => \DB::raw("quantity + {$qty}")]
        );
    }

    /**
     * Full stock set (e.g. after manual inventory count).
     */
    public function setStock(Product $product, int $qty, int $vendorId): void
    {
        $product->update(['stock_quantity' => $qty]);

        ProductStock::updateOrCreate(
            ['product_id' => $product->id, 'vendor_id' => $vendorId],
            ['quantity'   => $qty]
        );
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function checkLowStockAlert(Product $product, ProductStock $stock): void
    {
        if ($stock->isLow() || $stock->isOutOfStock()) {
            try {
                // Notify all admin users
                User::where('role', 'admin')->get()
                    ->each(fn ($admin) => $admin->notify(new LowStockAlertNotification($product, $stock->quantity)));
            } catch (\Throwable $e) {
                Log::warning('Low-stock alert notification failed: ' . $e->getMessage());
            }
        }
    }
}


