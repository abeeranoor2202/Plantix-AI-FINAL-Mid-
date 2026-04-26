<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use App\Services\Shared\ProductReviewEligibilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function __construct(
        private readonly ProductReviewEligibilityService $reviewEligibility,
    ) {}

    public function index(Request $request): View
    {
        $query = Product::with(['vendor.author', 'category', 'primaryImage', 'approvedReviews'])
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

        $products   = $query->orderBy(...$sort)->with(['category', 'primaryImage', 'vendor.author', 'stock'])->get();
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
            'vendor_photo'    => $p->vendor?->author?->profile_photo 
                                    ? Storage::url($p->vendor->author->profile_photo) 
                                    : null,
            'rating_avg'      => (float) ($p->rating_avg ?? 0),
            'track_stock'     => (bool) $p->track_stock,
            'stock_quantity'  => (int) ($p->stock?->quantity ?? $p->stock_quantity ?? 0),
            'is_available'    => (bool) ($p->stock?->is_available ?? true),
            'availability_label' => $p->track_stock
                ? (($p->stock?->is_available ?? true) === false
                    ? 'Unavailable'
                    : ((int) ($p->stock?->quantity ?? $p->stock_quantity ?? 0) <= 0 ? 'Out of Stock' : 'In Stock'))
                : 'In Stock',
            'image_url'       => $p->primaryImage
                                    ? Storage::url($p->primaryImage->path)
                                    : asset('assets/img/products/urea_sona.png'),
            'url'             => route('shop.single', $p->id),
        ])->values()->all();

        $priceMin   = (int) ($products->min('price') ?? 0);
        $priceMax   = (int) ($products->max('price') ?? 50000);
        $vendorList = $vendors->map(fn ($v) => ['id' => $v->id, 'name' => $v->title])->values()->all();

        return view('customer.shop', compact(
            'products', 'categories', 'vendors',
            'shopData', 'priceMin', 'priceMax', 'vendorList',
            'filterAttributes', 'rawAttrFilters', 'rawAttrMin', 'rawAttrMax'
        ));
    }

    public function show(int $id): View
    {
        $product  = Product::with([
            'vendor.author', 'category', 'images',
            'attributes.attribute', 'approvedReviews.user', 'stock',
        ])->where('is_active', true)->active()->findOrFail($id);

        $eligibleOrders = collect();
        $canReviewProduct = false;
        if (auth('web')->check()) {
            $viewer = auth('web')->user();
            $eligibleOrders = $this->reviewEligibility->eligibleOrders($viewer, $product);
            $canReviewProduct = $this->reviewEligibility->canReview($viewer, $product);
        }

        $related = Product::with('stock')->active()
                          ->where('is_active', true)
                          ->inStock()
                          ->where('category_id', $product->category_id)
                          ->where('id', '!=', $product->id)
                          ->limit(4)
                          ->get();

        return view('customer.shop-single', compact('product', 'related', 'eligibleOrders', 'canReviewProduct'));
    }
}
