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
use Illuminate\Http\JsonResponse;
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
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['nullable', 'in:0,1'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'rating_min' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ]);

        $query = Product::with(['category', 'stock'])
            ->where('vendor_id', $this->vendorId());

        if (! empty($filters['search'])) {
            $query->where(function ($productQuery) use ($filters): void {
                $productQuery->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('sku', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (array_key_exists('status', $filters) && $filters['status'] !== null && $filters['status'] !== '') {
            $query->where('is_active', (bool) ((int) $filters['status']));
        }

        if (! empty($filters['min_price'])) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        if (! empty($filters['rating_min'])) {
            $query->where('rating_avg', '>=', (float) $filters['rating_min']);
        }

        $products   = $query->latest()->paginate(20)->withQueryString();
        $categories = Category::with('createdByVendor')->orderBy('name')->get(['id', 'name', 'vendor_id']);

        return view('vendor.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::with('createdByVendor')->orderBy('name')->get(['id', 'name', 'vendor_id']);

        return view('vendor.products.form', [
            'categories' => $categories,
            'attributeValues' => old('attribute_values', []),
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
            'categories' => Category::with('createdByVendor')->orderBy('name')->get(['id', 'name', 'vendor_id']),
            'attributeValues' => old('attribute_values', $this->extractAttributeValues($product)),
        ]);
    }

    public function categoryAttributes(Category $category): JsonResponse
    {
        $attributes = $category->attributes()
            ->with('values')
            ->get()
            ->sortBy('pivot.sort_order')
            ->values()
            ->map(function (Attribute $attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name ?: $attribute->title,
                    'type' => $attribute->type ?: Attribute::TYPE_TEXT,
                    'unit' => $attribute->unit,
                    'is_required' => (bool) ($attribute->pivot->is_required ?? false),
                    'values' => $attribute->values
                        ->sortBy('sort_order')
                        ->values()
                        ->map(fn ($value) => [
                            'id' => $value->id,
                            'value' => $value->value,
                        ])
                        ->all(),
                ];
            })
            ->all();

        return response()->json([
            'category_id' => $category->id,
            'attributes' => $attributes,
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

            if ($attribute->type === Attribute::TYPE_BOOLEAN) {
                $normalizedBoolean = $this->normalizeBooleanAttributeValue($attributeInput);

                if ($isRequired && $normalizedBoolean === null) {
                    $errors["attribute_values.{$attribute->id}"] = "{$attributeName} is required.";
                    continue;
                }

                if ($normalizedBoolean === null) {
                    continue;
                }

                $rows[] = [
                    'product_id' => $product->id,
                    'attribute_id' => $attribute->id,
                    'value' => $normalizedBoolean ? '1' : '0',
                    'value_type' => $attribute->type,
                    'name' => $attributeName,
                    'type' => 'single',
                    'price' => 0,
                ];

                continue;
            }

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

    private function normalizeBooleanAttributeValue(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower(trim((string) $value));
        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return null;
    }

    private function extractAttributeValues(Product $product): array
    {
        return $product->attributes
            ->mapWithKeys(function (ProductAttribute $attributeValue) {
                if ($attributeValue->value_type === Attribute::TYPE_MULTI_SELECT) {
                    $decoded = json_decode((string) $attributeValue->value, true);
                    return [$attributeValue->attribute_id => is_array($decoded) ? $decoded : []];
                }

                return [$attributeValue->attribute_id => $attributeValue->value];
            })
            ->all();
    }
}
