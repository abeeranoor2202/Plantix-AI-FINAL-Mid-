<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CheckoutRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\Shared\CartCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartCheckoutService $checkout,
    ) {}

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

        $product = Product::active()->inStock()->findOrFail($request->product_id);
        $cart    = $this->getOrCreateCart($product->vendor_id);

        // Enforce single-vendor cart
        if ($cart->vendor_id !== $product->vendor_id) {
            $message = 'Your cart contains items from another store. Clear it first.';
            return $request->expectsJson()
                ? response()->json(['error' => $message], 409)
                : back()->withErrors(['cart' => $message]);
        }

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

    public function remove(int $itemId): JsonResponse|RedirectResponse
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

        $cart   = $this->getOrCreateCart();
        $coupon = Coupon::where('code', strtoupper(trim($request->code)))
                        ->where('is_active', true)
                        ->first();

        if (! $coupon || ! $coupon->isValid()) {
            $error = 'Invalid or expired coupon code.';
            return $request->expectsJson()
                ? response()->json(['error' => $error], 422)
                : back()->withErrors(['coupon' => $error]);
        }

        // Ensure coupon belongs to the same vendor as the cart (or is global)
        if ($coupon->vendor_id && $cart->vendor_id && $coupon->vendor_id !== $cart->vendor_id) {
            $error = 'This coupon is not valid for items in your cart.';
            return $request->expectsJson()
                ? response()->json(['error' => $error], 422)
                : back()->withErrors(['coupon' => $error]);
        }

        $subtotal = $cart->load('items')->subtotal;

        if ($cart->vendor_id && $coupon->min_order && $subtotal < $coupon->min_order) {
            $error = "Minimum order of " . number_format($coupon->min_order, 2) . " required for this coupon.";
            return $request->expectsJson()
                ? response()->json(['error' => $error], 422)
                : back()->withErrors(['coupon' => $error]);
        }

        $discount = $coupon->calculateDiscount($subtotal);

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
                // Stripe: create order + PI, return client_secret to JS
                $result = $this->checkout->initiate($user, $payload);
                return response()->json([
                    'client_secret'     => $result['client_secret'],
                    'order_id'          => $result['order']->id,
                    'payment_intent_id' => $result['order']->payment_intent_id,
                ]);
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

    private function getOrCreateCart(?int $vendorId = null): Cart
    {
        $user = auth('web')->user();

        $cart = Cart::where('user_id', $user->id)->first();

        if (! $cart && $vendorId) {
            $cart = Cart::create([
                'user_id'   => $user->id,
                'vendor_id' => $vendorId,
            ]);
        }

        return $cart ?? new Cart();
    }
}

