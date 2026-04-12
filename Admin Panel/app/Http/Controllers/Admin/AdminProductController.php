<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Attribute;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\Vendor;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Shared\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly StockService $stock,
    ) {}

    public function index(Request $request): View
    {
        $filters  = $request->only(['search', 'category_id', 'vendor_id', 'is_active', 'is_featured', 'sort', 'order']);
        $products = $this->products->paginate($filters, 20);

        return view('admin.products.index', [
            'products'   => $products,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'vendors'    => Vendor::orderBy('title')->get(['id', 'title']),
            'filters'    => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::with(['attributes.values'])->orderBy('name')->get(),
            'brands'     => Brand::active()->orderBy('name')->get(['id', 'name']),
            'vendors'    => Vendor::orderBy('title')->get(),
            'attributeMap' => $this->buildCategoryAttributeMap(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {

            $data    = $request->validated();
            $product = $this->products->create($data);

            $this->syncProductAttributes(
                $product,
                (int) ($data['category_id'] ?? 0),
                (array) $request->input('attribute_values', [])
            );

            // ── Primary image ─────────────────────────────────────────────────
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('products', 'public');
                $product->update(['image' => $path]);
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $path,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }

            // ── Gallery images ────────────────────────────────────────────────
            foreach ($request->file('gallery', []) as $index => $file) {
                $galleryPath = $file->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $galleryPath,
                    'is_primary' => false,
                    'sort_order' => $index + 1,
                ]);
            }

            // ── Initial stock ─────────────────────────────────────────────────
            if (! empty($data['stock_quantity'])) {
                $this->stock->setStock(
                    $product,
                    (int) $data['stock_quantity'],
                    (int) $data['vendor_id'],
                    auth('admin')->id(),
                    isset($data['low_stock_threshold']) ? (int) $data['low_stock_threshold'] : null,
                );
            }
        });

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product created successfully.');
    }

    public function show(int $id): View
    {
        $product = $this->products->findById($id);
        return view('admin.products.show', compact('product'));
    }

    public function edit(int $id): View
    {
        $product = $this->products->findById($id);

        return view('admin.products.edit', [
            'product'    => $product,
            'categories' => Category::with(['attributes.values'])->orderBy('name')->get(),
            'brands'     => Brand::active()->orderBy('name')->get(['id', 'name']),
            'vendors'    => Vendor::orderBy('title')->get(),
            'attributeMap' => $this->buildCategoryAttributeMap(),
        ]);
    }

    public function update(UpdateProductRequest $request, int $id): RedirectResponse
    {
        $product = $this->products->findById($id);
        $data    = $request->validated();

        DB::transaction(function () use ($request, $product, $data) {

            if ($request->hasFile('image')) {
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $request->file('image')->store('products', 'public');

                // Update primary image record
                ProductImage::where('product_id', $product->id)
                            ->where('is_primary', true)
                            ->update(['path' => $data['image']]);
            }

            foreach ($request->file('gallery', []) as $index => $file) {
                $galleryPath = $file->store('products', 'public');
                ProductImage::create([
                    'product_id' => $product->id,
                    'path'       => $galleryPath,
                    'is_primary' => false,
                    'sort_order' => $index + 100,
                ]);
            }

            $this->products->update($product, $data);

            $this->syncProductAttributes(
                $product,
                (int) ($data['category_id'] ?? $product->category_id),
                (array) $request->input('attribute_values', [])
            );

            if (isset($data['stock_quantity'])) {
                $this->stock->setStock(
                    $product,
                    (int) $data['stock_quantity'],
                    (int) $product->vendor_id,
                    auth('admin')->id(),
                    isset($data['low_stock_threshold']) ? (int) $data['low_stock_threshold'] : null,
                );
            }
        });

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $product = $this->products->findById($id);
        $this->products->delete($product);

        return redirect()->route('admin.products.index')
                         ->with('success', 'Product deleted successfully.');
    }

    public function toggleFeatured(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => ! $product->is_featured]);

        return back()->with('success', 'Featured status updated.');
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $next = ! $product->is_active;

        $product->update([
            'is_active' => $next,
            'status' => $next ? Product::STATUS_ACTIVE : Product::STATUS_INACTIVE,
        ]);

        return back()->with('success', 'Active status updated.');
    }

    public function toggleReturnable(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_returnable' => ! $product->is_returnable]);

        return back()->with('success', 'Returnable status updated.');
    }

    public function toggleRefundable(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $product->update(['is_refundable' => ! $product->is_refundable]);

        return back()->with('success', 'Refundable status updated.');
    }

    public function reviews(Request $request): View
    {
        $query = \App\Models\Review::with(['user', 'product', 'vendor'])->orderBy('created_at', 'desc');

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('product', function($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })->orWhereHas('user', function($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                });
            });
        }

        $reviews = $query->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function destroyReview(int $id): RedirectResponse
    {
        $review = \App\Models\Review::findOrFail($id);
        $review->delete();

        return back()->with('success', 'Review deleted successfully.');
    }

    private function buildCategoryAttributeMap(): array
    {
        return Category::with(['attributes.values'])
            ->get()
            ->mapWithKeys(function (Category $category) {
                return [
                    $category->id => $category->attributes
                        ->sortBy('pivot.sort_order')
                        ->values()
                        ->map(function (Attribute $attribute) {
                            return [
                                'id' => $attribute->id,
                                'name' => $attribute->name ?: $attribute->title,
                                'type' => $attribute->type,
                                'unit' => $attribute->unit,
                                'is_required' => (bool) ($attribute->pivot->is_required ?? false),
                                'values' => $attribute->values->pluck('value')->values()->all(),
                            ];
                        })
                        ->all(),
                ];
            })
            ->all();
    }

    private function syncProductAttributes(Product $product, int $categoryId, array $inputValues): void
    {
        if ($categoryId <= 0) {
            $product->attributes()->delete();
            return;
        }

        $category = Category::with(['attributes.values'])->find($categoryId);
        if (! $category) {
            $product->attributes()->delete();
            return;
        }

        $errors = [];
        $rows = [];

        foreach ($category->attributes as $attribute) {
            $attributeName = $attribute->name ?: $attribute->title;
            $attributeInput = $inputValues[$attribute->id] ?? null;
            $isRequired = (bool) ($attribute->pivot->is_required ?? false);

            if ($attribute->type === Attribute::TYPE_MULTI_SELECT) {
                $selectedValues = collect(is_array($attributeInput) ? $attributeInput : [])
                    ->map(fn ($value) => trim((string) $value))
                    ->filter()
                    ->values();

                if ($isRequired && $selectedValues->isEmpty()) {
                    $errors["attribute_values.{$attribute->id}"] = "{$attributeName} is required.";
                    continue;
                }

                if ($selectedValues->isNotEmpty()) {
                    $allowed = $attribute->values->pluck('value')->all();
                    foreach ($selectedValues as $selected) {
                        if (! in_array($selected, $allowed, true)) {
                            $errors["attribute_values.{$attribute->id}"] = "Invalid value selected for {$attributeName}.";
                            continue 2;
                        }
                    }

                    $rows[] = [
                        'product_id' => $product->id,
                        'attribute_id' => $attribute->id,
                        'value' => json_encode($selectedValues->all(), JSON_UNESCAPED_UNICODE),
                        'value_type' => $attribute->type,
                        'name' => $attributeName,
                        'type' => 'multiple',
                        'price' => 0,
                    ];
                }

                continue;
            }

            $value = is_array($attributeInput)
                ? ''
                : trim((string) ($attributeInput ?? ''));

            if ($isRequired && $value === '') {
                $errors["attribute_values.{$attribute->id}"] = "{$attributeName} is required.";
                continue;
            }

            if ($value === '') {
                continue;
            }

            if ($attribute->type === Attribute::TYPE_NUMBER && ! is_numeric($value)) {
                $errors["attribute_values.{$attribute->id}"] = "{$attributeName} must be numeric.";
                continue;
            }

            if ($attribute->type === Attribute::TYPE_SELECT) {
                $allowed = $attribute->values->pluck('value')->all();
                if (! in_array($value, $allowed, true)) {
                    $errors["attribute_values.{$attribute->id}"] = "Invalid value selected for {$attributeName}.";
                    continue;
                }
            }

            $rows[] = [
                'product_id' => $product->id,
                'attribute_id' => $attribute->id,
                'value' => $value,
                'value_type' => $attribute->type,
                'name' => $attributeName,
                'type' => 'single',
                'price' => 0,
            ];
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        ProductAttribute::where('product_id', $product->id)->delete();
        if (! empty($rows)) {
            ProductAttribute::insert($rows);
        }
    }
}

