<?php

namespace App\Services\Shared;

use App\Models\InventoryLog;
use App\Models\Stock;
use App\Models\StockMovement;

class InventoryService
{
    public function recordMovement(
        int $productId,
        ?int $vendorId,
        string $type,
        int $quantity,
        ?string $reference = null,
        array $legacyLog = []
    ): void {
        StockMovement::create([
            'product_id' => $productId,
            'vendor_id' => $vendorId,
            'type' => $type,
            'quantity' => max(0, $quantity),
            'reference' => $reference,
        ]);

        if (! empty($legacyLog)) {
            InventoryLog::create($legacyLog);
        }
    }

    public function recentMovements(int $limit = 50, ?int $vendorId = null)
    {
        return StockMovement::with(['product', 'vendor'])
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    public function analytics(?int $vendorId = null): array
    {
        $base = Stock::query()->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId));

        return [
            'total_products' => (clone $base)->count(),
            'out_of_stock' => (clone $base)->where('status', 'out_of_stock')->count(),
            'low_stock' => (clone $base)
                ->where('status', 'in_stock')
                ->whereRaw('(quantity - reserved_quantity) <= low_stock_threshold')
                ->count(),
        ];
    }
}
