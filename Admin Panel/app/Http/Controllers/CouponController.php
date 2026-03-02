<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Vendor;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth'); // Removed to avoid guard conflicts
    }

    public function index($id = '')
    {
        $coupons = Coupon::with('vendor')->orderBy('code')->get();
        return view('admin.coupons.index', compact('coupons', 'id'));
    }

    public function create($id = '')
    {
        $vendors = Vendor::orderBy('title')->get();
        return view('admin.coupons.create', compact('vendors', 'id'));
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
            'expires_at'   => 'nullable|date',
            'starts_at'    => 'nullable|date',
            'is_active'    => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['code']      = strtoupper(trim($data['code']));
        
        // Provide defaults for nullable numeric fields
        $data['min_order'] = $data['min_order'] ?? 0.00;
        $data['max_discount'] = $data['max_discount'] ?? 0.00;

        Coupon::create($data);
        return response()->json(['success' => true, 'redirect' => route('admin.coupons')]);
    }

    public function edit($id)
    {
        $coupon  = Coupon::findOrFail($id);
        $vendors = Vendor::orderBy('title')->get();
        return view('admin.coupons.edit', compact('coupon', 'vendors', 'id'));
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
            'expires_at'   => 'nullable|date',
            'starts_at'    => 'nullable|date',
            'is_active'    => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['code']      = strtoupper(trim($data['code']));
        
        // Provide defaults for nullable numeric fields
        $data['min_order'] = $data['min_order'] ?? 0.00;
        $data['max_discount'] = $data['max_discount'] ?? 0.00;

        $coupon->update($data);
        return response()->json(['success' => true, 'redirect' => route('admin.coupons')]);
    }

    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}