<?php

namespace App\Services\Shared;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function findAndValidateForCart(string $code, User $user, Cart $cart): Coupon
    {
        $coupon = Coupon::with(['products:id', 'categories:id', 'applicableVendors:id'])
            ->where('code', strtoupper(trim($code)))
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Invalid coupon code.',
            ]);
        }

        if (! $coupon->isValid()) {
            $message = 'This coupon has expired or is not yet valid.';
            if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
                $message = 'This coupon has expired.';
            }
            throw ValidationException::withMessages(['coupon_code' => $message]);
        }

        $perUserLimit = (int) ($coupon->per_user_limit ?? 1);
        if ($coupon->usageCountForUser($user->id) >= $perUserLimit) {
            throw ValidationException::withMessages([
                'coupon_code' => 'You have used this coupon the maximum number of times.',
            ]);
        }

        $eligibleSubtotal = $this->eligibleSubtotal($coupon, $cart);

        if ($eligibleSubtotal <= 0) {
            throw ValidationException::withMessages([
                'coupon_code' => 'This coupon is not eligible for the products currently in your cart.',
            ]);
        }

        if ($coupon->min_order && $eligibleSubtotal < (float) $coupon->min_order) {
            throw ValidationException::withMessages([
                'coupon_code' => 'Minimum order of ' . number_format((float) $coupon->min_order, 2) . ' required.',
            ]);
        }

        return $coupon;
    }

    public function eligibleSubtotal(Coupon $coupon, Cart $cart): float
    {
        $cart->loadMissing('items.product');

        if ($cart->items->isEmpty()) {
            return 0.0;
        }

        $productIds = $coupon->products->pluck('id')->all();
        $categoryIds = $coupon->categories->pluck('id')->all();
        $vendorIds = $coupon->applicableVendors->pluck('id')->all();

        if ((int) ($coupon->vendor_id ?? 0) > 0) {
            $vendorIds[] = (int) $coupon->vendor_id;
        }

        $productMap = array_fill_keys($productIds, true);
        $categoryMap = array_fill_keys($categoryIds, true);
        $vendorMap = array_fill_keys($vendorIds, true);

        $isScoped = ! empty($productMap) || ! empty($categoryMap) || ! empty($vendorMap);

        return (float) $cart->items->sum(function ($item) use ($isScoped, $productMap, $categoryMap, $vendorMap) {
            $product = $item->product;
            if (! $product) {
                return 0;
            }

            if (! $isScoped) {
                return $item->unit_price * $item->quantity;
            }

            $match = false;

            if (! empty($productMap) && isset($productMap[(int) $product->id])) {
                $match = true;
            }

            if (! $match && ! empty($categoryMap) && isset($categoryMap[(int) $product->category_id])) {
                $match = true;
            }

            if (! $match && ! empty($vendorMap) && isset($vendorMap[(int) $product->vendor_id])) {
                $match = true;
            }

            return $match ? ($item->unit_price * $item->quantity) : 0;
        });
    }

    public function calculateDiscountForCart(Coupon $coupon, Cart $cart): float
    {
        $eligibleSubtotal = $this->eligibleSubtotal($coupon, $cart);
        return $coupon->calculateDiscount($eligibleSubtotal);
    }
}
