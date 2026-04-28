<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CheckoutRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\Shared\CartCheckoutService;
use App\Services\Shared\CouponService;
use App\Services\Shared\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class CartController extends Controller
{
    public function __construct(
        private readonly CartCheckoutService $checkout,
        private readonly CouponService $couponService,
        private readonly StockService $stockService,
    ) {}

    // ── JSON helpers (public — no auth gate, return 0 for guests) ─────────────

    public function count(): JsonResponse
    {
        if (! auth('web')->check()) {
            return response()->json(['count' => 0]);
        }

        $cart = Cart::where('user_id', auth('web')->id())->withCount('items')->first();
        return response()->json(['count' => $cart ? (int) $cart->items->sum('quantity') : 0]);
    }

    public function mini(): JsonResponse
    {
        if (! auth('web')->check()) {
            return response()->json(['count' => 0, 'items' => [], 'subtotal' => 0]);
        }

        $cart = Cart::where('user_id', auth('web')->id())
            ->with('items.product.primaryImage')
            ->first();

        if (! $cart) {
            return response()->json(['count' => 0, 'items' => [], 'subtotal' => 0]);
        }

        $items = $cart->items->map(fn ($item) => [
            'id'       => $item->id,
            'name'     => $item->product?->name ?? 'Product',
            'price'    => (float) $item->unit_price,
            'quantity' => (int) $item->quantity,
            'subtotal' => round((float) $item->unit_price * $item->quantity, 2),
            'image'    => $item->product?->primaryImage
                ? \Illuminate\Support\Facades\Storage::url($item->product->primaryImage->path)
                : null,
            'url'      => route('shop.single', $item->product_id),
        ]);

        return response()->json([
            'count'    => (int) $cart->items->sum('quantity'),
            'items'    => $items->take(5)->values(),
            'subtotal' => $cart->subtotal,
        ]);
    }

    // ── Cart management ────────────────────────────────────────────────────────

    public function index(): View
    {
        $cart = $this->getUserCart() ?? new Cart(['user_id' => auth('web')->id()]);
        $globalCoupons = Coupon::where('is_active', true)
            ->where('is_visible_to_all', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END, expires_at ASC')
            ->limit(3)
            ->get(['id', 'code']);

        return view('customer.cart', [
            'cart' => $cart->load('items.product.primaryImage'),
            'globalCoupons' => $globalCoupons,
        ]);
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:99',
        ]);

        $product = Product::with('stock')->where('is_active', true)->active()->findOrFail($request->product_id);
        $cart    = $this->getOrCreateCart((int) $product->vendor_id);

        $existing = CartItem::where('cart_id', $cart->id)
                            ->where('product_id', $product->id)
                            ->first();

        $this->stockService->reserveStock(
            product: $product,
            qty: (int) $request->quantity,
            reference: 'cart:' . $cart->id,
            initiatedBy: auth('web')->id(),
        );

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

        $totalItems = $cart->fresh()->total_items;

        return $request->expectsJson()
            ? response()->json(['success' => true, 'cart_count' => $totalItems])
            : back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, int $itemId): JsonResponse|RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:0|max:99']);

        $cart = $this->getUserCart();
        abort_if(! $cart, 404);

        $item = CartItem::with('product')
            ->where('cart_id', $cart->id)
            ->findOrFail($itemId);
        $oldQty = (int) $item->quantity;
        $newQty = (int) $request->quantity;
        $ref = 'cart:' . $item->cart_id;

        if ($newQty === 0) {
            if ($item->product) {
                $this->stockService->releaseReservedStock($item->product, $oldQty, $ref, auth('web')->id());
            }
            $item->delete();
        } else {
            $delta = $newQty - $oldQty;
            if ($delta > 0 && $item->product) {
                $this->stockService->reserveStock($item->product, $delta, $ref, auth('web')->id());
            } elseif ($delta < 0 && $item->product) {
                $this->stockService->releaseReservedStock($item->product, abs($delta), $ref, auth('web')->id());
            }
            $item->update(['quantity' => $newQty]);
        }

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : back()->with('success', 'Cart updated.');
    }

    public function remove(string $itemId): JsonResponse|RedirectResponse
    {
        $cart = $this->getUserCart();
        abort_if(! $cart, 404);

        $item = CartItem::with('product')
            ->where('cart_id', $cart->id)
            ->findOrFail($itemId);
        if ($item->product) {
            $this->stockService->releaseReservedStock(
                product: $item->product,
                qty: (int) $item->quantity,
                reference: 'cart:' . $item->cart_id,
                initiatedBy: auth('web')->id(),
            );
        }
        $item->delete();

        return request()->expectsJson()
            ? response()->json(['success' => true])
            : back()->with('success', 'Item removed.');
    }

    public function clear(): RedirectResponse
    {
        $user = auth('web')->user();
        Cart::where('user_id', $user->id)->with('items.product')->get()
            ->each(function ($cart) {
                foreach ($cart->items as $item) {
                    if ($item->product) {
                        $this->stockService->releaseReservedStock(
                            product: $item->product,
                            qty: (int) $item->quantity,
                            reference: 'cart:' . $cart->id,
                            initiatedBy: auth('web')->id(),
                        );
                    }
                }
                $cart->items()->delete();
                $cart->delete();
            });

        return redirect()->route('cart')->with('success', 'Cart cleared.');
    }

    // ── Coupon ────────────────────────────────────────────────────────────────

    /**
     * Validate and store a coupon code in the session.
     * Route: POST /cart/coupon
     */
    public function applyCoupon(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate(['code' => 'required|string|max:100']);

        $cart = $this->getUserCart();
        if (! $cart) {
            $message = 'Your cart is empty. Add items before applying a coupon.';
            return $request->expectsJson()
                ? response()->json(['error' => $message], 422)
                : back()->withErrors(['coupon' => $message]);
        }
        $user = auth('web')->user();

        try {
            $coupon = $this->couponService->findAndValidateForCart($request->code, $user, $cart);
            $discount = $this->couponService->calculateDiscountForCart($coupon, $cart);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $message = collect($e->errors())->flatten()->first() ?? 'Coupon validation failed.';
            return $request->expectsJson()
                ? response()->json(['error' => $message], 422)
                : back()->withErrors(['coupon' => $message]);
        }

        session([
            'coupon_code'     => $coupon->code,
            'coupon_id'       => $coupon->id,
            'coupon_discount' => $discount,
        ]);

        $message = "Coupon applied! You save " . number_format($discount, 2) . ".";

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => $message, 'discount' => $discount])
            : back()->with('success', $message);
    }

    /**
     * Remove an applied coupon from the session.
     * Route: DELETE /cart/coupon
     */
    public function removeCoupon(): JsonResponse|RedirectResponse
    {
        session()->forget(['coupon_code', 'coupon_id', 'coupon_discount']);

        return request()->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Coupon removed.'])
            : back()->with('success', 'Coupon removed.');
    }

    // ── Checkout ──────────────────────────────────────────────────────────────

    public function checkout(): View|RedirectResponse
    {
        $user = auth('web')->user();
        $cart = Cart::with('items.product.primaryImage')
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart')->withErrors(['cart' => 'Your cart is empty.']);
        }
        $globalCoupons = Coupon::where('is_active', true)
            ->where('is_visible_to_all', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderByRaw('CASE WHEN expires_at IS NULL THEN 1 ELSE 0 END, expires_at ASC')
            ->limit(3)
            ->get(['id', 'code']);

        return view('customer.checkout', [
            'cart'      => $cart,
            'addresses' => $user->addresses,
            'globalCoupons' => $globalCoupons,
            'stripeEnabled' => (bool) \App\Models\Setting::get('stripe_enabled', true),
            'codEnabled'    => (bool) \App\Models\Setting::get('cod_enabled', true),
        ]);
    }

    public function placeOrder(CheckoutRequest $request): JsonResponse|RedirectResponse
    {
        $user    = auth('web')->user();
        $payload = $request->validated();

        try {
            if ($payload['payment_method'] === 'stripe') {
                // Stripe: create order + Checkout Session and redirect to Stripe only
                $result = $this->checkout->initiate($user, $payload);

                /** @var \App\Models\Order $order */
                $order = $result['order'];

                $checkoutUrl = (string) ($result['checkout_url'] ?? '');
                if ($checkoutUrl === '') {
                    throw new \RuntimeException('Stripe Checkout URL is missing.');
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'checkout_url'      => $checkoutUrl,
                        'order_id'          => $order->id,
                        'payment_intent_id' => $order->payment_intent_id,
                    ]);
                }

                return redirect()->away($checkoutUrl);
            }

            // COD: deduct stock immediately, redirect to success
            $order = $this->checkout->placeCodOrder($user, $payload);

            return redirect()->route('order.success', ['id' => $order->id])
                             ->with('success', 'Order placed successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return request()->expectsJson()
                ? response()->json(['errors' => $e->errors()], 422)
                : back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('placeOrder error', ['message' => $e->getMessage()]);
            return request()->expectsJson()
                ? response()->json(['error' => 'Order could not be placed. Please try again.'], 500)
                : back()->withErrors(['order' => 'Order could not be placed. Please try again.']);
        }
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function getOrCreateCart(int $vendorId): Cart
    {
        $user = auth('web')->user();
        $cart = $this->getUserCart();
        if (! $cart) {
            return Cart::create([
                'user_id' => $user->id,
                'vendor_id' => $vendorId,
            ]);
        }

        return $cart;
    }

    private function getUserCart(): ?Cart
    {
        return Cart::query()
            ->where('user_id', auth('web')->id())
            ->latest('id')
            ->first();
    }
}

