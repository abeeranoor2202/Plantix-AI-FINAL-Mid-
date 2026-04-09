<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorProductRequest;
use App\Http\Requests\Vendor\UpdateVendorProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Shared\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class VendorProductController extends Controller
{
    public function __construct(
        private readonly StockService $stock,
    ) {}

    private function vendorId(): int
    {
        return auth('vendor')->user()->vendor->id;
    }

    public function index(Request $request): View
    {
        $query = Product::with(['category', 'stock'])
            ->where('vendor_id', $this->vendorId());

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('vendor.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('vendor.products.form', compact('categories'));
    }

    public function show(int $id): View
    {
        $product = Product::with(['category', 'images', 'stock'])
            ->where('vendor_id', $this->vendorId())
            ->findOrFail($id);

        return view('vendor.products.show', compact('product'));
    }

    public function store(StoreVendorProductRequest $request): RedirectResponse
    {
        $vendorId = $this->vendorId();

        DB::transaction(function () use ($request, $vendorId) {
            $data              = $request->validated();
            $data['vendor_id'] = $vendorId;

            $product = Product::create($data);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $product->update(['image' => $path]);
                ProductImage::create(['product_id' => $product->id, 'path' => $path, 'is_primary' => true]);
            }

            foreach ($request->file('gallery', []) as $i => $file) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $file->store('products', 'public'),
                    'sort_order' => $i + 1,
                ]);
            }

            if (! empty($data['stock_quantity'])) {
                $this->stock->setStock($product, (int) $data['stock_quantity'], $vendorId);
            }
        });

        return redirect()->route('vendor.products.index')
                         ->with('success', 'Product added successfully.');
    }

    public function edit(int $id): View
    {
        $product = Product::where('vendor_id', $this->vendorId())->findOrFail($id);
        return view('vendor.products.form', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateVendorProductRequest $request, int $id): RedirectResponse
    {
        $product = Product::where('vendor_id', $this->vendorId())->findOrFail($id);
        $data    = $request->validated();

        DB::transaction(function () use ($request, $product, $data) {
            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($data);

            if (isset($data['stock_quantity'])) {
                $this->stock->setStock($product, (int) $data['stock_quantity'], $product->vendor_id);
            }
        });

        return redirect()->route('vendor.products.index')
                         ->with('success', 'Product updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Product::where('vendor_id', $this->vendorId())->findOrFail($id)->delete();
        return redirect()->route('vendor.products.index')
                         ->with('success', 'Product deleted.');
    }
}
