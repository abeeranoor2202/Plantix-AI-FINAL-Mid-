<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Shared\CartCheckoutService;
use App\Services\Shared\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerCartApiController extends Controller
{
    public function __construct(
        private readonly CartCheckoutService $checkout,
        private readonly CouponService $couponService,
    ) {}

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
        $cart    = $this->getOrCreateCart($user, (int) $product->vendor_id);

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

        $user = $request->user();
        $cart = $this->getOrCreateCart($user)->load('items.product.primaryImage');

        try {
            $coupon = $this->couponService->findAndValidateForCart($request->code, $user, $cart);
            $discount = $this->couponService->calculateDiscountForCart($coupon, $cart);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Coupon validation failed.',
            ], 422);
        }

        $subtotal = (float) $cart->subtotal;
        $total = max(0, $subtotal - $discount);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully!',
            'coupon'  => [
                'code'  => $coupon->code,
                'type'  => $coupon->type,
                'value' => $coupon->value,
                'discount' => $discount,
            ],
            'pricing' => [
                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'total' => round($total, 2),
            ],
            'cart' => $this->cartPayload($cart),
        ]);
    }

    // ── Remove coupon ────────────────────────────────────────────────────────

    public function removeCoupon(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Coupon removed.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getOrCreateCart($user, ?int $vendorId = null): Cart
    {
        $cart = Cart::query()->where('user_id', $user->id)->latest('id')->first();

        if (! $cart) {
            if (! $vendorId) {
                throw ValidationException::withMessages([
                    'cart' => 'A vendor is required before creating a cart.',
                ]);
            }

            return Cart::create([
                'user_id' => $user->id,
                'vendor_id' => $vendorId,
            ]);
        }

        if ($vendorId && (int) $cart->vendor_id !== $vendorId) {
            throw ValidationException::withMessages([
                'cart' => 'Your cart contains items from another vendor. Clear the cart before adding this item.',
            ]);
        }

        return $cart;
    }

    private function cartPayload(Cart $cart): array
    {
        $subtotal = $cart->items->sum(fn ($i) => $i->unit_price * $i->quantity);
        $discount = 0;

        return [
            'id'       => $cart->id,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total'    => max(0, $subtotal - $discount),
            'count'    => $cart->items->sum('quantity'),
            'coupon'   => null,
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
