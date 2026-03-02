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

        $coupon = Coupon::where('code', strtoupper($request->code))
                        ->where('is_active', true)
                        ->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired coupon.'], 422);
        }

        $cart = $this->getOrCreateCart($request->user());
        $cart->update(['coupon_id' => $coupon->id, 'discount_amount' => 0]);

        return response()->json([
            'success' => true,
            'coupon'  => [
                'code'  => $coupon->code,
                'type'  => $coupon->type,
                'value' => $coupon->value,
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
