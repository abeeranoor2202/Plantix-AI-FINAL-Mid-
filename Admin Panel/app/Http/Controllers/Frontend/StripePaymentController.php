<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use App\Services\Shared\CartCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * StripePaymentController
 *
 * Responsibilities:
 *  - createIntent    POST /payments/stripe/intent  → creates order + PI for new checkout
 *  - webhook         POST /stripe/webhook          → handles Stripe events (no CSRF)
 *  - success         GET  /payment/success         → redirect landing page
 */
class StripePaymentController extends Controller
{
    public function __construct(
        private readonly CartCheckoutService $checkout,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Checkout initiate: create order + PI in one atomic call
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Called by checkout.blade.php via AJAX (or form POST) at payment step.
     * Creates a pending_payment order + returns client_secret.
     *
     * Route: POST /checkout/stripe/initiate
     */
    public function initiateCheckout(CheckoutRequest $request): JsonResponse|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            $result = $this->checkout->initiate($user, $request->validated());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $request->expectsJson()
                ? response()->json(['errors' => $e->errors()], 422)
                : back()->withErrors($e->errors());
        } catch (\Throwable $e) {
            Log::error('Checkout initiate error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $request->expectsJson()
                ? response()->json(['error' => 'Checkout failed. Please try again.'], 500)
                : back()->withErrors(['checkout' => 'Checkout failed. Please try again.']);
        }

        /** @var Order $order */
        $order = $result['order'];

        if ($request->expectsJson()) {
            return response()->json([
                'client_secret'     => $result['client_secret'],
                'checkout_url'      => $result['checkout_url'] ?? null,
                'order_id'          => $order->id,
                'payment_intent_id' => $order->payment_intent_id,
            ]);
        }

        if (! empty($result['checkout_url'])) {
            return redirect()->away($result['checkout_url']);
        }

        // For blade: store PI in session then redirect to payment page
        session([
            'pending_order_id'  => $order->id,
            'stripe_secret'     => $result['client_secret'],
        ]);

        return redirect()->route('checkout.pay', ['order' => $order->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stripe payment page (shows Stripe.js payment form)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Route: GET /checkout/pay/{order}
     */
    public function showPaymentPage(Order $order): \Illuminate\View\View|RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ((int) $order->user_id !== (int) $user->id) {
            abort(403);
        }

        // Must still be awaiting payment
        if (! $order->isPendingPayment()) {
            if ($order->payment_status === 'paid') {
                return redirect()->route('order.success', $order->id);
            }
            return redirect()->route('checkout')->withErrors(['order' => 'This order cannot be paid.']);
        }

        $clientSecret  = session('stripe_secret') ?? null;
        $publishableKey = config('services.stripe.key');

        $order->loadMissing('items.product');

        return view('frontend.checkout.stripe-payment', compact('order', 'clientSecret', 'publishableKey'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Payment confirmation is webhook-driven only
    // Route: POST /checkout/pay/{order}
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Keep frontend flow pending; payment state is updated only by Stripe webhook.
     */
    public function processOrderPayment(Request $request, Order $order): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ((int) $order->user_id !== (int) $user->id) {
            abort(403);
        }

        if (! $order->isPendingPayment()) {
            if ($order->payment_status === 'paid') {
                return redirect()->route('order.success', $order->id);
            }
            return redirect()->route('checkout')->withErrors(['order' => 'This order cannot be paid.']);
        }

        // Intentionally no status mutation here.
        return redirect()->route('checkout.pay', $order->id)
            ->with('info', 'Payment is pending confirmation. Your order will update automatically after Stripe webhook verification.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Legacy createIntent endpoint (kept for backwards-compat with API clients)
    // Creates a PI for an already-existing pending_payment order
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Route: POST /payments/stripe/intent
     */
    public function createIntent(Request $request): JsonResponse
    {
        $request->validate(['order_id' => 'required|integer|exists:orders,id']);

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $order = Order::findOrFail($request->order_id);

        if ((int) $order->user_id !== (int) $user->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order is already paid.'], 422);
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            if ($order->payment_intent_id) {
                $intent = \Stripe\PaymentIntent::retrieve($order->payment_intent_id);
            } else {
                $intent = \Stripe\PaymentIntent::create(
                    [
                        'amount'   => (int) round($order->total * 100),
                        'currency' => strtolower(config('plantix.currency_code', 'usd')),
                        'metadata' => [
                            'order_id'     => $order->id,
                            'order_number' => $order->order_number,
                            'user_id'      => $user->id,
                        ],
                        'automatic_payment_methods' => ['enabled' => true],
                    ],
                    ['idempotency_key' => 'order-intent-' . $order->id]
                );

                $order->update(['payment_intent_id' => $intent->id]);
            }

            return response()->json([
                'client_secret'     => $intent->client_secret,
                'payment_intent_id' => $intent->id,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe createIntent error', ['order_id' => $order->id, 'message' => $e->getMessage()]);
            return response()->json(['error' => 'Payment service unavailable.'], 503);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Success redirect page
    // ─────────────────────────────────────────────────────────────────────────

    public function success(Request $request)
    {
        $orderId = $request->query('order_id') ?? session('pending_order_id');
        $order   = $orderId ? Order::find($orderId) : null;

        if ($order && (int) $order->user_id === (int) Auth::id() && $order->payment_status === 'paid') {
            return view('customer.payment-success', compact('order'));
        }

        return redirect()->route('orders')->with('info', 'Payment is being processed.');
    }

}
