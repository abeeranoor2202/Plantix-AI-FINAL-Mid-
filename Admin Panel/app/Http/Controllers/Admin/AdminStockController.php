<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Vendor;
use App\Services\Shared\InventoryService;
use App\Services\Shared\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AdminStockController
 *
 * Gives administrators a global view of all product stock levels across all
 * vendors, with the ability to update quantities and set low-stock thresholds.
 */
class AdminStockController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly InventoryService $inventory,
    ) {}

    /**
     * Show the global stock overview.
     * Route: GET /admin/stock
     */
    public function index(Request $request): View
    {
        $query = Stock::with(['product.category', 'vendor']);

        if ($request->filled('search')) {
            $query->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->filled('stock_status')) {
            match ($request->stock_status) {
                'low'      => $query->whereRaw('quantity > 0 AND quantity <= low_stock_threshold'),
                'out'      => $query->where('quantity', '<=', 0),
                'in_stock' => $query->where('quantity', '>', 0),
                default    => null,
            };
        }

        $stocks = $query->latest()->paginate(30)->withQueryString();

        $vendors = Vendor::query()
            ->whereIn('id', Stock::query()->select('vendor_id')->distinct())
            ->orderByRaw('COALESCE(NULLIF(title, ""), NULLIF(owner_name, ""), id)')
            ->get(['id', 'title', 'owner_name'])
            ->map(function (Vendor $vendor) {
                $vendor->name = trim((string) ($vendor->title ?: $vendor->owner_name ?: ('Vendor #' . $vendor->id)));
                return $vendor;
            });

        $summary = $this->inventory->analytics();
        $movements = $this->inventory->recentMovements(25);

        return view('admin.stock.index', compact('stocks', 'vendors', 'summary', 'movements'));
    }

    /**
     * Show the edit form for a single stock record.
     * Route: GET /admin/stock/{id}/edit
     */
    public function edit(int $id): View
    {
        $stock = Stock::with(['product', 'vendor'])->findOrFail($id);
        return view('admin.stock.edit', compact('stock'));
    }

    /**
     * Show a read-only stock record.
     * Route: GET /admin/stock/{id}
     */
    public function show(int $id): View
    {
        $stock = Stock::with(['product', 'vendor'])->findOrFail($id);
        return view('admin.stock.show', compact('stock'));
    }

    /**
     * Update a stock record.
     * Route: PUT /admin/stock/{id}
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'quantity'            => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'sku'                 => 'nullable|string|max:100',
        ]);

        $stock = Stock::with('product')->findOrFail($id);

        if ($request->filled('sku')) {
            $stock->product?->update(['sku' => (string) $request->sku]);
        }

        $this->stockService->setStock(
            product: $stock->product,
            qty: (int) $request->quantity,
            vendorId: (int) $stock->vendor_id,
            initiatedBy: auth('admin')->id(),
            threshold: $request->filled('low_stock_threshold') ? (int) $request->low_stock_threshold : null,
        );

        return redirect()->route('admin.stock.index')
                         ->with('success', "Stock for \"{$stock->product->name}\" updated.");
    }

    /**
     * Delete a stock record.
     * Route: DELETE /admin/stock/{id}
     */
    public function destroy(int $id): RedirectResponse
    {
        $stock = Stock::with('product')->findOrFail($id);

        $hasQuantity = (int) $stock->quantity > 0;
        $productActive = (bool) ($stock->product?->is_active ?? false);

        if ($hasQuantity || $productActive) {
            return back()->with('error', 'Stock can only be deleted when quantity is zero and the product is inactive.');
        }

        $stock->delete();

        return back()->with('success', 'Stock deleted successfully.');
    }

}
