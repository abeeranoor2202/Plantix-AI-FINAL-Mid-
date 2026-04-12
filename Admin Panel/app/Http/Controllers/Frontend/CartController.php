<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CheckoutRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\Shared\CartCheckoutService;
use App\Services\Shared\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartCheckoutService $checkout,
        private readonly CouponService $couponService,
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
        $cart = $this->getOrCreateCart();
        return view('customer.cart', ['cart' => $cart->load('items.product.primaryImage')]);
    }

    public function add(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:99',
        ]);

        $product = Product::where('is_active', true)->active()->inStock()->findOrFail($request->product_id);
        $cart    = $this->getOrCreateCart();

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

        $totalItems = $cart->fresh()->total_items;

        return $request->expectsJson()
            ? response()->json(['success' => true, 'cart_count' => $totalItems])
            : back()->with('success', 'Added to cart.');
    }

    public function update(Request $request, int $itemId): JsonResponse|RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:0|max:99']);

        $item = CartItem::findOrFail($itemId);

        if ($request->quantity === 0) {
            $item->delete();
        } else {
            $item->update(['quantity' => $request->quantity]);
        }

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : back()->with('success', 'Cart updated.');
    }

    public function remove(string $itemId): JsonResponse|RedirectResponse
    {
        CartItem::findOrFail($itemId)->delete();

        return request()->expectsJson()
            ? response()->json(['success' => true])
            : back()->with('success', 'Item removed.');
    }

    public function clear(): RedirectResponse
    {
        $user = auth('web')->user();
        Cart::where('user_id', $user->id)->with('items')->get()
            ->each(function ($cart) {
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

        $cart = $this->getOrCreateCart();
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

    public function checkout(): View
    {
        $user = auth('web')->user();
        $cart = Cart::with('items.product.primaryImage')->where('user_id', $user->id)->firstOrFail();

        return view('customer.checkout', [
            'cart'      => $cart,
            'addresses' => $user->addresses,
        ]);
    }

    public function placeOrder(CheckoutRequest $request): JsonResponse|RedirectResponse
    {
        $user    = auth('web')->user();
        $payload = $request->validated();

        try {
            if ($payload['payment_method'] === 'stripe') {
                // Stripe: create order + PI
                $result = $this->checkout->initiate($user, $payload);

                /** @var \App\Models\Order $order */
                $order = $result['order'];

                if ($request->expectsJson()) {
                    return response()->json([
                        'client_secret'     => $result['client_secret'],
                        'order_id'          => $order->id,
                        'payment_intent_id' => $order->payment_intent_id,
                    ]);
                }

                // Regular form POST — store PI secret in session, redirect to payment page
                session([
                    'pending_order_id' => $order->id,
                    'stripe_secret'    => $result['client_secret'],
                ]);

                return redirect()->route('checkout.pay', ['order' => $order->id]);
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

    private function getOrCreateCart(): Cart
    {
        $user = auth('web')->user();

        return Cart::firstOrCreate(['user_id' => $user->id]);
    }
}

