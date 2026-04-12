<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth'); // Removed to avoid guard conflicts
    }

    public function index($id = '')
    {
        $coupons = Coupon::with(['vendor', 'products', 'categories', 'applicableVendors'])
            ->withCount('usages')
            ->orderBy('code')
            ->get();

        $usageTotals = DB::table('coupon_usages')
            ->select('coupon_id', DB::raw('SUM(discount_amount) as discount_total'))
            ->groupBy('coupon_id')
            ->pluck('discount_total', 'coupon_id');

        $coupons->each(function ($coupon) use ($usageTotals) {
            $coupon->discount_total = (float) ($usageTotals[$coupon->id] ?? 0);
        });

        return view('admin.coupons.index', compact('coupons', 'id'));
    }

    public function create($id = '')
    {
        $vendors = Vendor::orderBy('title')->get();
        $products = Product::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        return view('admin.coupons.create', compact('vendors', 'products', 'categories', 'id'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'         => 'required|string|max:60|unique:coupons,code',
            'vendor_id'    => 'nullable|exists:vendors,id',
            'type'         => 'required|in:percentage,fixed',
            'value'        => 'required|numeric|min:0',
            'min_order'    => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit'  => 'nullable|integer|min:1',
            'per_user_limit'  => 'nullable|integer|min:1',
            'expires_at'   => 'nullable|date',
            'starts_at'    => 'nullable|date',
            'is_active'    => 'nullable|boolean',
            'is_visible_to_all' => 'nullable|boolean',
            'product_ids'     => 'nullable|array',
            'product_ids.*'   => 'integer|exists:products,id',
            'category_ids'    => 'nullable|array',
            'category_ids.*'  => 'integer|exists:categories,id',
            'vendor_ids'      => 'nullable|array',
            'vendor_ids.*'    => 'integer|exists:vendors,id',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_visible_to_all'] = $request->boolean('is_visible_to_all');
        $data['code']      = strtoupper(trim($data['code']));
        
        // Provide defaults for nullable numeric fields
        $data['min_order'] = $data['min_order'] ?? 0.00;
        $data['max_discount'] = $data['max_discount'] ?? 0.00;
        $data['per_user_limit'] = $data['per_user_limit'] ?? 1;

        $coupon = Coupon::create($data);
        $coupon->products()->sync($request->input('product_ids', []));
        $coupon->categories()->sync($request->input('category_ids', []));
        $coupon->applicableVendors()->sync($request->input('vendor_ids', []));

        return response()->json(['success' => true, 'redirect' => route('admin.coupons')]);
    }

    public function edit($id)
    {
        $coupon  = Coupon::with(['products', 'categories', 'applicableVendors'])->findOrFail($id);
        $vendors = Vendor::orderBy('title')->get();
        $products = Product::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        return view('admin.coupons.edit', compact('coupon', 'vendors', 'products', 'categories', 'id'));
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $data = $request->validate([
            'code'         => 'required|string|max:60|unique:coupons,code,' . $id,
            'vendor_id'    => 'nullable|exists:vendors,id',
            'type'         => 'required|in:percentage,fixed',
            'value'        => 'required|numeric|min:0',
            'min_order'    => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit'  => 'nullable|integer|min:1',
            'per_user_limit'  => 'nullable|integer|min:1',
            'expires_at'   => 'nullable|date',
            'starts_at'    => 'nullable|date',
            'is_active'    => 'nullable|boolean',
            'is_visible_to_all' => 'nullable|boolean',
            'product_ids'     => 'nullable|array',
            'product_ids.*'   => 'integer|exists:products,id',
            'category_ids'    => 'nullable|array',
            'category_ids.*'  => 'integer|exists:categories,id',
            'vendor_ids'      => 'nullable|array',
            'vendor_ids.*'    => 'integer|exists:vendors,id',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['is_visible_to_all'] = $request->boolean('is_visible_to_all');
        $data['code']      = strtoupper(trim($data['code']));
        
        // Provide defaults for nullable numeric fields
        $data['min_order'] = $data['min_order'] ?? 0.00;
        $data['max_discount'] = $data['max_discount'] ?? 0.00;
        $data['per_user_limit'] = $data['per_user_limit'] ?? 1;

        $coupon->update($data);
        $coupon->products()->sync($request->input('product_ids', []));
        $coupon->categories()->sync($request->input('category_ids', []));
        $coupon->applicableVendors()->sync($request->input('vendor_ids', []));

        return response()->json(['success' => true, 'redirect' => route('admin.coupons')]);
    }

    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function toggle($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return response()->json(['success' => true, 'active' => $coupon->is_active]);
    }
}