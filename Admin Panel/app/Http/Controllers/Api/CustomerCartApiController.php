<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\Shared\CartCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerCartApiController extends Controller
{
    public function __construct(private readonly CartCheckoutService $checkout) {}

    // ── Get cart ──────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());

        return response()->json([
            'success' => true,
            'cart'    => $this->cartPayload($cart->load('items.product')),
        ]);
    }

    // ── Add item ─────────────────────────────────────────────────────────────

    public function addItem(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:99',
        ]);

        $user    = $request->user();
        $product = Product::active()->inStock()->findOrFail($request->product_id);
        $cart    = $this->getOrCreateCart($user);

        $existing = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $product->id)
                            ->first();

        if ($existing) {
            $existing->increment('quantity', $request->quantity);
        } else {
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
                'unit_price' => $product->effective_price,
            ]);
        }

        return response()->json([
            'success'    => true,
            'cart'       => $this->cartPayload($cart->fresh()->load('items.product')),
            'cart_count' => $cart->fresh()->total_items,
        ]);
    }

    // ── Update item quantity ──────────────────────────────────────────────────

    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:0|max:99']);

        $cart = $this->getOrCreateCart($request->user());
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($itemId);

        if ($request->quantity === 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $request->quantity]);
        }

        return response()->json([
            'success' => true,
            'cart'    => $this->cartPayload($cart->fresh()->load('items.product')),
        ]);
    }

    // ── Remove item ──────────────────────────────────────────────────────────

    public function removeItem(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->getOrCreateCart($request->user());
        CartItem::where('cart_id', $cart->id)->findOrFail($itemId)->delete();

        return response()->json([
            'success' => true,
            'cart'    => $this->cartPayload($cart->fresh()->load('items.product')),
        ]);
    }

    // ── Clear cart ────────────────────────────────────────────────────────────

    public function clear(Request $request): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($cart) {
            $cart->items()->delete();
            $cart->update(['coupon_id' => null, 'discount_amount' => 0]);
        }

        return response()->json(['success' => true, 'message' => 'Cart cleared.']);
    }

    // ── Apply coupon ─────────────────────────────────────────────────────────

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:50']);

        $coupon = Coupon::where('code', strtoupper(trim($request->code)))->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 422);
        }

        // Check if coupon is active and not expired
        if (! $coupon->isValid()) {
            $message = 'This coupon has expired or is not yet valid.';
            if ($coupon->expires_at && now()->gt($coupon->expires_at)) {
                $message = 'This coupon has expired.';
            }
            return response()->json(['success' => false, 'message' => $message], 422);
        }

        // Check per-user usage limit
        $user = $request->user();
        $perUserLimit = (int) ($coupon->per_user_limit ?? 1);
        if ($coupon->usageCountForUser($user->id) >= $perUserLimit) {
            return response()->json(['success' => false, 'message' => 'You have used this coupon the maximum number of times.'], 422);
        }

        // Get or create cart
        $cart = $this->getOrCreateCart($user);

        // Check if coupon is store-specific and cart has items from different store
        if ($coupon->vendor_id && $cart->items->isNotEmpty()) {
            $cartVendorIds = $cart->items->pluck('product.vendor_id')->unique()->filter();
            if ($cartVendorIds->isNotEmpty() && ! $cartVendorIds->contains($coupon->vendor_id)) {
                return response()->json(['success' => false, 'message' => 'This coupon is not valid for items in your cart.'], 422);
            }
        }

        // Check minimum order amount
        $subtotal = (float) $cart->subtotal;
        if ($coupon->min_order && $subtotal < (float) $coupon->min_order) {
            return response()->json(['success' => false, 'message' => "Minimum order of " . number_format($coupon->min_order, 2) . " required for this coupon."], 422);
        }

        // Calculate and apply discount
        $discount = $coupon->calculateDiscount($subtotal);
        $cart->update(['coupon_id' => $coupon->id, 'discount_amount' => $discount]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'coupon'  => [
                'code'  => $coupon->code,
                'type'  => $coupon->type,
                'value' => $coupon->value,
                'discount' => $discount,
            ],
            'cart' => $this->cartPayload($cart->fresh()->load('items.product')),
        ]);
    }

    // ── Remove coupon ────────────────────────────────────────────────────────

    public function removeCoupon(Request $request): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($cart) {
            $cart->update(['coupon_id' => null, 'discount_amount' => 0]);
        }

        return response()->json(['success' => true, 'message' => 'Coupon removed.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getOrCreateCart($user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    private function cartPayload(Cart $cart): array
    {
        $subtotal = $cart->items->sum(fn ($i) => $i->unit_price * $i->quantity);
        $discount = 0;

        if ($cart->coupon) {
            if ($cart->coupon->type === 'percent') {
                $discount = round($subtotal * $cart->coupon->value / 100, 2);
            } elseif ($cart->coupon->type === 'fixed') {
                $discount = min($cart->coupon->value, $subtotal);
            }
        }

        return [
            'id'       => $cart->id,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => max(0, $subtotal - $discount),
            'count'    => $cart->items->sum('quantity'),
            'coupon'   => $cart->coupon ? [
                'code'  => $cart->coupon->code,
                'type'  => $cart->coupon->type,
                'value' => $cart->coupon->value,
            ] : null,
            'items' => $cart->items->map(fn ($item) => [
                'id'         => $item->id,
                'product_id' => $item->product_id,
                'name'       => optional($item->product)->name,
                'image'      => optional($item->product->primaryImage)->url,
                'price'      => $item->unit_price,
                'quantity'   => $item->quantity,
                'line_total' => round($item->unit_price * $item->quantity, 2),
            ])->values()->toArray(),
        ];
    }
}
