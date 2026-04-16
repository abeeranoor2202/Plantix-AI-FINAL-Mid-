<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VendorCouponController extends Controller
{
    private function vendorId(): int
    {
        return auth('vendor')->user()->vendor->id;
    }

    // ── List ─────────────────────────────────────────────────────────────────

    public function index(): View
    {
        $coupons = Coupon::where('vendor_id', $this->vendorId())
                         ->withCount('usages')
                         ->latest()
                         ->paginate(20);

        return view('vendor.coupons.index', compact('coupons'));
    }

    // ── Create ───────────────────────────────────────────────────────────────

    public function create(): View
    {
        return view('vendor.coupons.form', ['coupon' => null]);
    }

    // ── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['vendor_id'] = $this->vendorId();

        if (empty($data['code'])) {
            $data['code'] = strtoupper(Str::random(8));
        }

        Coupon::create($data);

        return redirect()->route('vendor.coupons.index')
                         ->with('success', 'Coupon created successfully.');
    }

    // ── Edit ─────────────────────────────────────────────────────────────────

    public function edit(int $id): View
    {
        $coupon = Coupon::where('vendor_id', $this->vendorId())->findOrFail($id);
        return view('vendor.coupons.form', compact('coupon'));
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, int $id): RedirectResponse
    {
        $coupon = Coupon::where('vendor_id', $this->vendorId())->findOrFail($id);
        $coupon->update($this->validated($request));

        return redirect()->route('vendor.coupons.index')
                         ->with('success', 'Coupon updated.');
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function destroy(int $id): RedirectResponse
    {
        Coupon::where('vendor_id', $this->vendorId())->findOrFail($id)->delete();

        return redirect()->route('vendor.coupons.index')
                         ->with('success', 'Coupon deleted.');
    }

    // ── Toggle Active ─────────────────────────────────────────────────────────

    public function toggle(int $id): RedirectResponse
    {
        $coupon = Coupon::where('vendor_id', $this->vendorId())->findOrFail($id);
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('success', 'Coupon status updated.');
    }

    // ── Shared validation ─────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'code'         => ['nullable', 'string', 'max:50'],
            'type'         => ['required', 'in:percentage,fixed'],
            'value'        => ['required', 'numeric', 'min:0.01'],
            'min_order'    => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit'  => ['nullable', 'integer', 'min:1'],
            'starts_at'    => ['nullable', 'date'],
            'expires_at'   => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active'    => ['boolean'],
        ]);

        // Convert null values to defaults to match database constraints
        $validated['min_order'] = $validated['min_order'] ?? 0.00;
        $validated['max_discount'] = $validated['max_discount'] ?? 0.00;
        $validated['usage_limit'] = $validated['usage_limit'] ?? null;

        return $validated;
    }
}
