<?php

namespace App\Services\Shared;

use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Notifications\LowStockAlertNotification;
use App\Notifications\OutOfStockAlertNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
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
    public function __construct(
        private readonly InventoryService $inventory,
    ) {}

    /**
     * Assert that the product has enough stock for the requested quantity.
     * Throws a ValidationException if stock is insufficient.
     */
    public function assertSufficientStock(Product $product, int $requestedQty): void
    {
        if (! $product->track_stock) {
            return; // unlimited stock products
        }

        $stock = $this->ensureStock($product);
        if (! $stock->is_available) {
            throw ValidationException::withMessages([
                'quantity' => "{$product->name} is unavailable.",
            ]);
        }

        $available = $stock->available_quantity;

        if ($available < $requestedQty) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$available} unit(s) of \"{$product->name}\" are available.",
            ]);
        }
    }

    public function assertPhysicalStock(Product $product, int $requestedQty): void
    {
        if (! $product->track_stock) {
            return;
        }

        $stock = $this->ensureStock($product);
        if (! $stock->is_available) {
            throw ValidationException::withMessages([
                'quantity' => "{$product->name} is unavailable.",
            ]);
        }

        if ($stock->quantity < $requestedQty) {
            throw ValidationException::withMessages([
                'quantity' => "Only {$stock->quantity} unit(s) of \"{$product->name}\" are available.",
            ]);
        }
    }

    public function reserveStock(Product $product, int $qty, string $reference, ?int $initiatedBy = null): void
    {
        if (! $product->track_stock || $qty <= 0) {
            return;
        }

        DB::transaction(function () use ($product, $qty, $reference, $initiatedBy) {
            $stock = Stock::where('product_id', $product->id)
                ->where('vendor_id', $product->vendor_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $stock->is_available) {
                throw ValidationException::withMessages([
                    'quantity' => "{$product->name} is unavailable.",
                ]);
            }

            if ($stock->available_quantity < $qty) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$stock->available_quantity} unit(s) of \"{$product->name}\" are available.",
                ]);
            }

            $stock->reserved_quantity += $qty;
            $this->syncStatus($stock);

            $this->inventory->recordMovement(
                productId: $product->id,
                vendorId: $product->vendor_id,
                type: StockMovement::TYPE_RESERVED,
                quantity: $qty,
                reference: $reference,
                legacyLog: [
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'initiated_by' => $initiatedBy,
                    'type' => InventoryLog::TYPE_ADJUSTMENT,
                    'quantity_before' => $stock->quantity,
                    'quantity_change' => 0,
                    'quantity_after' => $stock->quantity,
                    'notes' => "Reserved {$qty} unit(s). Ref: {$reference}",
                ],
            );
        });
    }

    public function releaseReservedStock(Product $product, int $qty, string $reference, ?int $initiatedBy = null): void
    {
        if (! $product->track_stock || $qty <= 0) {
            return;
        }

        DB::transaction(function () use ($product, $qty, $reference, $initiatedBy) {
            $stock = Stock::where('product_id', $product->id)
                ->where('vendor_id', $product->vendor_id)
                ->lockForUpdate()
                ->firstOrFail();

            $released = min($qty, $stock->reserved_quantity);
            if ($released <= 0) {
                return;
            }

            $stock->reserved_quantity -= $released;
            $this->syncStatus($stock);

            $this->inventory->recordMovement(
                productId: $product->id,
                vendorId: $product->vendor_id,
                type: StockMovement::TYPE_RELEASED,
                quantity: $released,
                reference: $reference,
                legacyLog: [
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'initiated_by' => $initiatedBy,
                    'type' => InventoryLog::TYPE_ADJUSTMENT,
                    'quantity_before' => $stock->quantity,
                    'quantity_change' => 0,
                    'quantity_after' => $stock->quantity,
                    'notes' => "Released {$released} reserved unit(s). Ref: {$reference}",
                ],
            );
        });
    }

    /**
     * Decrement stock after a confirmed sale.
     * Must be called inside a DB::transaction().
     */
    public function decrementStock(
        Product $product,
        int     $qty,
        ?int    $orderId    = null,
        ?int    $initiatedBy = null
    ): void {
        if (! $product->track_stock) {
            return;
        }

        DB::transaction(function () use ($product, $qty, $orderId, $initiatedBy) {
            $stock = Stock::where('product_id', $product->id)
                ->where('vendor_id', $product->vendor_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $stock->is_available) {
                throw ValidationException::withMessages([
                    'quantity' => "{$product->name} is unavailable.",
                ]);
            }

            if ($stock->quantity < $qty) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock for '{$product->name}'.",
                ]);
            }

            $before = $stock->quantity;
            $consumedReserved = min($qty, $stock->reserved_quantity);
            $stock->quantity -= $qty;
            $stock->reserved_quantity -= $consumedReserved;
            $this->syncStatus($stock);

            $product->update([
                'stock_quantity' => $stock->quantity,
                'low_stock_threshold' => $stock->low_stock_threshold,
            ]);
            $this->syncLegacyProductStock($product, $stock);

            $this->inventory->recordMovement(
                productId: $product->id,
                vendorId: $product->vendor_id,
                type: StockMovement::TYPE_OUT,
                quantity: $qty,
                reference: $orderId ? "order:{$orderId}" : 'manual',
                legacyLog: [
                    'product_id'      => $product->id,
                    'vendor_id'       => $product->vendor_id,
                    'order_id'        => $orderId,
                    'initiated_by'    => $initiatedBy,
                    'type'            => InventoryLog::TYPE_SALE,
                    'quantity_before' => $before,
                    'quantity_change' => -$qty,
                    'quantity_after'  => $stock->quantity,
                    'notes'           => "Sale: {$qty} unit(s) deducted" . ($orderId ? " for order #{$orderId}" : ''),
                ],
            );

            $this->checkLowStockAlert($product->fresh(), $stock->fresh());
        });
    }

    /**
     * Restore stock when an order is cancelled or rejected.
     * Must be called inside a DB::transaction().
     */
    public function restoreStock(
        Product $product,
        int     $qty,
        string  $reason      = 'cancel',  // 'cancel' | 'return'
        ?int    $orderId     = null,
        ?int    $returnId    = null,
        ?int    $initiatedBy = null
    ): void {
        if (! $product->track_stock) {
            return;
        }

        DB::transaction(function () use ($product, $qty, $reason, $orderId, $returnId, $initiatedBy) {
            $stock = Stock::where('product_id', $product->id)
                ->where('vendor_id', $product->vendor_id)
                ->lockForUpdate()
                ->firstOrFail();

            $before = $stock->quantity;
            $stock->quantity += $qty;
            $this->syncStatus($stock);

            $product->update(['stock_quantity' => $stock->quantity]);
            $this->syncLegacyProductStock($product, $stock);

            $legacyType = $reason === 'return' ? InventoryLog::TYPE_RETURN : InventoryLog::TYPE_CANCEL;
            $this->inventory->recordMovement(
                productId: $product->id,
                vendorId: $product->vendor_id,
                type: StockMovement::TYPE_IN,
                quantity: $qty,
                reference: $orderId ? "order:{$orderId}" : 'manual',
                legacyLog: [
                    'product_id'      => $product->id,
                    'vendor_id'       => $product->vendor_id,
                    'order_id'        => $orderId,
                    'return_id'       => $returnId,
                    'initiated_by'    => $initiatedBy,
                    'type'            => $legacyType,
                    'quantity_before' => $before,
                    'quantity_change' => $qty,
                    'quantity_after'  => $stock->quantity,
                    'notes'           => ucfirst($reason) . ": {$qty} unit(s) restored" . ($orderId ? " for order #{$orderId}" : ''),
                ],
            );
        });
    }

    /**
     * Restock a product (admin or vendor).
     */
    public function restock(Product $product, int $qty, int $vendorId, ?int $initiatedBy = null): void
    {
        DB::transaction(function () use ($product, $qty, $vendorId, $initiatedBy) {
            $stock = $this->ensureStock($product, $vendorId, true);
            $before = $stock->quantity;
            $stock->quantity += $qty;
            $this->syncStatus($stock);

            $product->update(['stock_quantity' => $stock->quantity]);
            $this->syncLegacyProductStock($product, $stock);

            $this->inventory->recordMovement(
                productId: $product->id,
                vendorId: $vendorId,
                type: StockMovement::TYPE_IN,
                quantity: $qty,
                reference: 'manual',
                legacyLog: [
                    'product_id'      => $product->id,
                    'vendor_id'       => $vendorId,
                    'initiated_by'    => $initiatedBy,
                    'type'            => InventoryLog::TYPE_RESTOCK,
                    'quantity_before' => $before,
                    'quantity_change' => $qty,
                    'quantity_after'  => $stock->quantity,
                    'notes'           => "Manual restock: {$qty} unit(s) added",
                ],
            );
        });
    }

    /**
     * Full stock set (e.g. after manual inventory count).
     */
    public function setStock(Product $product, int $qty, int $vendorId, ?int $initiatedBy = null, ?int $threshold = null): void
    {
        DB::transaction(function () use ($product, $qty, $vendorId, $initiatedBy, $threshold) {
            $stock = $this->ensureStock($product, $vendorId, true);
            $before = $stock->quantity;

            $stock->quantity = max(0, $qty);
            if ($threshold !== null) {
                $stock->low_stock_threshold = max(0, $threshold);
            }
            if ($stock->reserved_quantity > $stock->quantity) {
                $stock->reserved_quantity = $stock->quantity;
            }
            $this->syncStatus($stock);

            $product->update([
                'stock_quantity' => $stock->quantity,
                'low_stock_threshold' => $stock->low_stock_threshold,
            ]);
            $this->syncLegacyProductStock($product, $stock);

            $this->inventory->recordMovement(
                productId: $product->id,
                vendorId: $vendorId,
                type: $qty >= $before ? StockMovement::TYPE_IN : StockMovement::TYPE_OUT,
                quantity: abs($qty - $before),
                reference: 'manual',
                legacyLog: [
                    'product_id'      => $product->id,
                    'vendor_id'       => $vendorId,
                    'initiated_by'    => $initiatedBy,
                    'type'            => InventoryLog::TYPE_ADJUSTMENT,
                    'quantity_before' => $before,
                    'quantity_change' => $qty - $before,
                    'quantity_after'  => $stock->quantity,
                    'notes'           => "Manual adjustment: set to {$qty} unit(s)",
                ],
            );

            $this->checkLowStockAlert($product->fresh(), $stock->fresh());
        });
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function checkLowStockAlert(Product $product, Stock $stock): void
    {
        if ($stock->isLow() || $stock->isOutOfStock()) {
            try {
                $admins = User::where('role', 'admin')->get();
                $notification = $stock->isOutOfStock()
                    ? new OutOfStockAlertNotification($product)
                    : new LowStockAlertNotification($product, $stock->available_quantity);

                $admins->each(fn ($admin) => $admin->notify($notification));

                if ($product->vendor?->author) {
                    $product->vendor->author->notify($notification);
                }
            } catch (\Throwable $e) {
                Log::warning('Low-stock alert notification failed: ' . $e->getMessage());
            }
        }
    }

    private function ensureStock(Product $product, ?int $vendorId = null, bool $forUpdate = false): Stock
    {
        $vendorId ??= (int) $product->vendor_id;

        $query = Stock::where('product_id', $product->id)->where('vendor_id', $vendorId);
        if ($forUpdate) {
            $query->lockForUpdate();
        }

        $stock = $query->first();
        if ($stock) {
            return $stock;
        }

        return Stock::create([
            'product_id' => $product->id,
            'vendor_id' => $vendorId,
            'quantity' => max(0, (int) $product->stock_quantity),
            'reserved_quantity' => 0,
            'low_stock_threshold' => max(0, (int) ($product->low_stock_threshold ?? config('plantix.low_stock_threshold', 5))),
            'status' => ((int) $product->stock_quantity) > 0 ? 'in_stock' : 'out_of_stock',
            'is_available' => true,
        ]);
    }

    private function syncStatus(Stock $stock): void
    {
        $stock->status = $stock->quantity <= 0 ? 'out_of_stock' : 'in_stock';
        $stock->save();
    }

    private function syncLegacyProductStock(Product $product, Stock $stock): void
    {
        ProductStock::updateOrCreate(
            ['product_id' => $product->id, 'vendor_id' => $stock->vendor_id],
            [
                'quantity' => $stock->quantity,
                'low_stock_threshold' => $stock->low_stock_threshold,
                'sku' => $product->sku,
            ]
        );
    }
}



