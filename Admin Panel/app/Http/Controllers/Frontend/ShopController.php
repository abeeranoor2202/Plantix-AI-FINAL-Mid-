<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['vendor', 'category', 'primaryImage', 'approvedReviews'])
            ->where('is_active', true)
            ->active()
            ->inStock();

        $rawAttrFilters = (array) $request->input('attr', []);
        $rawAttrMin = (array) $request->input('attr_min', []);
        $rawAttrMax = (array) $request->input('attr_max', []);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('vendor')) {
            $query->whereHas('vendor', fn($q) => $q->where('slug', $request->vendor));
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        foreach ($rawAttrFilters as $attributeId => $value) {
            $attributeId = (int) $attributeId;
            if ($attributeId <= 0) {
                continue;
            }

            $value = is_array($value) ? '' : trim((string) $value);
            if ($value === '') {
                continue;
            }

            $query->whereHas('attributes', function ($attributeQuery) use ($attributeId, $value) {
                $attributeQuery
                    ->where('attribute_id', $attributeId)
                    ->where(function ($valueQuery) use ($value) {
                        $valueQuery->where('value', $value)
                            ->orWhere('value', 'like', '%"' . $value . '"%');
                    });
            });
        }

        foreach ($rawAttrMin as $attributeId => $minimum) {
            $attributeId = (int) $attributeId;
            if ($attributeId <= 0 || $minimum === null || $minimum === '') {
                continue;
            }

            $minNumber = (float) $minimum;
            $query->whereHas('attributes', function ($attributeQuery) use ($attributeId, $minNumber) {
                $attributeQuery
                    ->where('attribute_id', $attributeId)
                    ->whereRaw('CAST(value AS DECIMAL(12,4)) >= ?', [$minNumber]);
            });
        }

        foreach ($rawAttrMax as $attributeId => $maximum) {
            $attributeId = (int) $attributeId;
            if ($attributeId <= 0 || $maximum === null || $maximum === '') {
                continue;
            }

            $maxNumber = (float) $maximum;
            $query->whereHas('attributes', function ($attributeQuery) use ($attributeId, $maxNumber) {
                $attributeQuery
                    ->where('attribute_id', $attributeId)
                    ->whereRaw('CAST(value AS DECIMAL(12,4)) <= ?', [$maxNumber]);
            });
        }

        $sort = match ($request->sort) {
            'price_asc'  => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
            'newest'     => ['created_at', 'desc'],
            'popular'    => ['sort_order', 'asc'],
            default      => ['created_at', 'desc'],
        };

        $products   = $query->orderBy(...$sort)->with(['category', 'primaryImage', 'vendor'])->get();
        $categories = Category::orderBy('name')->get();
        $vendors    = Vendor::where('is_active', true)->where('is_approved', true)->orderBy('title')->get();
        $filterAttributes = Attribute::with('values')
            ->whereHas('categories')
            ->orderBy('name')
            ->orderBy('title')
            ->get();

        $shopData = $products->map(fn ($p) => [
            'id'              => $p->id,
            'name'            => $p->name,
            'subtitle'        => null,
            'description'     => $p->description,
            'price'           => (float) $p->price,
            'effective_price' => (float) $p->effective_price,
            'discount_price'  => $p->discount_price ? (float) $p->discount_price : null,
            'is_on_sale'      => (bool) $p->is_on_sale,
            'category'        => $p->category?->name,
            'vendor'          => $p->vendor?->title,
            'vendor_id'       => $p->vendor_id,
            'rating_avg'      => (float) ($p->rating_avg ?? 0),
            'image_url'       => $p->primaryImage
                                    ? Storage::url($p->primaryImage->path)
                                    : asset('assets/img/products/urea_sona.png'),
            'url'             => route('shop.single', $p->id),
        ])->values()->all();

        $priceMin   = (int) ($products->min('price') ?? 0);
        $priceMax   = (int) ($products->max('price') ?? 50000);
        $vendorList = $vendors->map(fn ($v) => ['id' => $v->id, 'name' => $v->title])->values()->all();
        $brandList  = $vendorList; // brands map to vendors in this catalogue

        return view('customer.shop', compact(
            'products', 'categories', 'vendors',
            'shopData', 'priceMin', 'priceMax', 'vendorList', 'brandList',
            'filterAttributes', 'rawAttrFilters', 'rawAttrMin', 'rawAttrMax'
        ));
    }

    public function show(int $id): View
    {
        $product  = Product::with([
            'vendor', 'category', 'images',
            'attributes.attribute', 'approvedReviews.user', 'stock',
        ])->where('is_active', true)->active()->findOrFail($id);

        $eligibleOrders = collect();
        if (auth('web')->check()) {
            $eligibleOrders = Order::query()
                ->where('user_id', auth('web')->id())
                ->whereIn('status', [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])
                ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                ->latest()
                ->get(['id', 'order_number', 'status', 'created_at']);
        }

        $related = Product::active()
                          ->where('is_active', true)
                          ->inStock()
                          ->where('category_id', $product->category_id)
                          ->where('id', '!=', $product->id)
                          ->limit(4)
                          ->get();

        return view('customer.shop-single', compact('product', 'related', 'eligibleOrders'));
    }
}
