<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\StoreVendorProductRequest;
use App\Http\Requests\Vendor\UpdateVendorProductRequest;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Services\Shared\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
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

        if ($request->filled('status')) {
            $query->where('is_active', (bool) $request->boolean('status'));
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('vendor.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::with(['attributes.values'])->orderBy('name')->get();

        return view('vendor.products.form', [
            'categories' => $categories,
            'attributeMap' => $this->buildCategoryAttributeMap(),
        ]);
    }

    public function show(int $id): View
    {
        $product = Product::with(['category', 'images', 'stock', 'attributes.attribute'])
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

            $this->syncProductAttributes(
                $product,
                (int) ($data['category_id'] ?? 0),
                (array) $request->input('attribute_values', [])
            );

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
                $this->stock->setStock(
                    $product,
                    (int) $data['stock_quantity'],
                    $vendorId,
                    auth('vendor')->id(),
                    isset($data['low_stock_threshold']) ? (int) $data['low_stock_threshold'] : null,
                );
            }
        });

        return redirect()->route('vendor.products.index')
                         ->with('success', 'Product added successfully.');
    }

    public function edit(int $id): View
    {
        $product = Product::with('attributes.attribute')
            ->where('vendor_id', $this->vendorId())
            ->findOrFail($id);

        return view('vendor.products.form', [
            'product'    => $product,
            'categories' => Category::with(['attributes.values'])->orderBy('name')->get(),
            'attributeMap' => $this->buildCategoryAttributeMap(),
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
                    auth('vendor')->id(),
                    isset($data['low_stock_threshold']) ? (int) $data['low_stock_threshold'] : null,
                );
            }
        });

        return redirect()->route('vendor.products.index')
                         ->with('success', 'Product updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Product::where('vendor_id', $this->vendorId())->findOrFail($id)->delete();
        return redirect()->route('vendor.products.index')
                         ->with('success', 'Product deleted successfully.');
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        return $this->toggleActive($id);
    }

    public function toggleActive(int $id): RedirectResponse
    {
        $product = Product::where('vendor_id', $this->vendorId())->findOrFail($id);

        $product->update([
            'is_active' => ! $product->is_active,
        ]);

        return back()->with('success', 'Product status updated successfully.');
    }

    public function toggleReturnable(int $id): RedirectResponse
    {
        $product = Product::where('vendor_id', $this->vendorId())->findOrFail($id);

        $product->update([
            'is_returnable' => ! $product->is_returnable,
        ]);

        return back()->with('success', 'Return eligibility updated successfully.');
    }

    public function toggleRefundable(int $id): RedirectResponse
    {
        $product = Product::where('vendor_id', $this->vendorId())->findOrFail($id);

        $product->update([
            'is_refundable' => ! $product->is_refundable,
        ]);

        return back()->with('success', 'Refund eligibility updated successfully.');
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
