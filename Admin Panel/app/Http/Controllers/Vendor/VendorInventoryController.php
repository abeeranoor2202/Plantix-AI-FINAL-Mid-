<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Services\Shared\InventoryService;
use App\Services\Shared\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * VendorInventoryController
 *
 * Lets vendors monitor and adjust stock levels for their own products.
 */
class VendorInventoryController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
        private readonly InventoryService $inventory,
    ) {}

    private function vendorId(): int
    {
        return auth('vendor')->user()->vendor->id;
    }

    /**
     * Show inventory report for all vendor products.
     * Route: GET /vendor/inventory
     */
    public function index(Request $request): View
    {
        $vendorId = $this->vendorId();

        $query = Stock::with(['product'])
            ->where('vendor_id', $vendorId);

        if ($request->filled('search')) {
            $query->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('stock_status')) {
            match ($request->stock_status) {
                'low'      => $query->whereRaw('quantity > 0 AND quantity <= low_stock_threshold'),
                'out'      => $query->where('quantity', '<=', 0),
                'in_stock' => $query->where('quantity', '>', 0),
                default    => null,
            };
        }

        $stocks = $query->latest()->paginate(25)->withQueryString();

        $summary = $this->inventory->analytics($vendorId);
        $summary['total_stock_value'] = Stock::where('stocks.vendor_id', $vendorId)
            ->join('products', 'stocks.product_id', '=', 'products.id')
            ->selectRaw('SUM(stocks.quantity * products.price) as total')
            ->value('total') ?? 0;
        $movements = $this->inventory->recentMovements(20, $vendorId);

        return view('vendor.inventory.index', compact('stocks', 'summary', 'movements'));
    }

    /**
     * Update the stock quantity for a product.
     * Route: POST /vendor/inventory/{id}/update
     */
    public function update(Request $request, int $productId): RedirectResponse
    {
        $request->validate([
            'quantity'            => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        $stock = Stock::where('product_id', $productId)
            ->where('vendor_id', $this->vendorId())
            ->with('product')
            ->firstOrFail();

        $product = $stock->product ?? \App\Models\Product::withTrashed()->find($productId);

        if (! $product) {
            return back()->withErrors(['error' => 'Product not found for this stock record.']);
        }

        $this->stockService->setStock(
            product: $product,
            qty: (int) $request->quantity,
            vendorId: (int) $stock->vendor_id,
            initiatedBy: auth('vendor')->id(),
            threshold: $request->filled('low_stock_threshold') ? (int) $request->low_stock_threshold : null,
        );

        return back()->with('success', 'Stock updated successfully.');
    }

    /**
     * Delete a zero-quantity stock record for the vendor's product.
     * Route: DELETE /vendor/inventory/{id}
     */
    public function destroy(int $productId): RedirectResponse
    {
        $stock = Stock::where('product_id', $productId)
            ->where('vendor_id', $this->vendorId())
            ->firstOrFail();

        if ((int) $stock->quantity > 0 || (int) $stock->reserved_quantity > 0) {
            return back()->withErrors([
                'stock' => 'Cannot delete a stock record that still has units. Set quantity to 0 first.',
            ]);
        }

        // Remove movement history for this product/vendor, then delete the record
        $stock->movements()->delete();
        $stock->delete();

        return back()->with('success', 'Stock record deleted successfully.');
    }
}
