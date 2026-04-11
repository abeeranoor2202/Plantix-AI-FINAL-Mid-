<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Vendor;
use App\Services\Shared\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * AdminStockController
 *
 * Gives administrators a global view of all product stock levels across all
 * vendors, with the ability to adjust quantities and set low-stock thresholds.
 */
class AdminStockController extends Controller
{
    public function __construct(
        private readonly StockService $stockService,
    ) {}

    /**
     * Show the global stock overview.
     * Route: GET /admin/stock
     */
    public function index(Request $request): View
    {
        $query = ProductStock::with(['product.category', 'vendor']);

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

        $stocks  = $query->latest()->paginate(30)->withQueryString();
        $vendors = Vendor::orderBy('title')->get(['id', 'title as name']);

        $summary = [
            'total_products' => ProductStock::count(),
            'out_of_stock'   => ProductStock::where('quantity', '<=', 0)->count(),
            'low_stock'      => ProductStock::whereRaw('quantity > 0 AND quantity <= low_stock_threshold')->count(),
        ];

        return view('admin.stock.index', compact('stocks', 'vendors', 'summary'));
    }

    /**
     * Show the edit form for a single stock record.
     * Route: GET /admin/stock/{id}/edit
     */
    public function edit(int $id): View
    {
        $stock = ProductStock::with(['product', 'vendor'])->findOrFail($id);
        return view('admin.stock.edit', compact('stock'));
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

        $stock = ProductStock::findOrFail($id);
        $stock->update(array_filter([
            'quantity'            => $request->quantity,
            'low_stock_threshold' => $request->low_stock_threshold,
            'sku'                 => $request->sku,
        ], fn ($v) => $v !== null));

        return redirect()->route('admin.stock.index')
                         ->with('success', "Stock for \"{$stock->product->name}\" updated.");
    }

    /**
     * Manually adjust (increment / decrement) stock quantity.
     * Route: POST /admin/stock/{id}/adjust
     */
    public function adjust(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'adjustment' => 'required|integer',   // positive = add, negative = subtract
            'note'       => 'nullable|string|max:500',
        ]);

        $stock = ProductStock::findOrFail($id);
        $newQty = max(0, $stock->quantity + (int) $request->adjustment);
        $stock->update(['quantity' => $newQty]);

        return back()->with('success', "Stock adjusted. New quantity: {$newQty}.");
    }
}
