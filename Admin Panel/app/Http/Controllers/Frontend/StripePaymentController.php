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
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

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
                'order_id'          => $order->id,
                'payment_intent_id' => $order->payment_intent_id,
            ]);
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
    // Simulated payment confirmation (demo / test mode)
    // Route: POST /checkout/pay/{order}
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Process the demo card form submission and advance the order to paid status.
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

        $request->validate([
            'card_name'   => 'required|string|max:100',
            'card_number' => 'required|string',
            'card_exp'    => 'required|string',
            'card_cvc'    => 'required|string',
        ]);

        try {
            if ($order->payment_intent_id) {
                // Use CartCheckoutService to confirm — mirrors what the webhook does
                $this->checkout->confirmPayment($order->payment_intent_id);
            } else {
                // No real PI — advance directly (pure demo env)
                $order->update([
                    'status'         => \App\Models\Order::STATUS_PROCESSING,
                    'payment_status' => 'paid',
                ]);
            }        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Payment record not yet created (e.g. no real Stripe webhook) — advance directly
            $order->update([
                'status'         => \App\Models\Order::STATUS_CONFIRMED,
                'payment_status' => 'paid',
            ]);        } catch (\Throwable $e) {
            Log::error('processOrderPayment error', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            return redirect()->route('checkout.pay', $order->id)
                             ->with('error', 'Payment could not be processed. Please try again.');
        }

        session()->forget(['pending_order_id', 'stripe_secret']);

        return redirect()->route('order.success', $order->id)
                         ->with('success', 'Payment successful! Your order has been placed.');
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
    // Stripe Webhook
    // Route: POST /stripe/webhook  (excluded from CSRF in Kernel.php)
    // ─────────────────────────────────────────────────────────────────────────

    public function webhook(Request $request): Response
    {
        $payload   = $request->getContent(); // MUST use raw content — not parsed input
        $sigHeader = $request->header('Stripe-Signature', '');
        $secret    = config('services.stripe.webhook_secret');

        if (empty($secret)) {
            Log::error('STRIPE_WEBHOOK_SECRET not configured.');
            return response('Server configuration error.', 500);
        }

        // 1. Verify signature
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload.', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature failed', ['ip' => $request->ip()]);
            return response('Invalid signature.', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event->type, 'id' => $event->id]);

        // 2. Route to handler — always return 200 to prevent Stripe retries
        try {
            match ($event->type) {
                'payment_intent.succeeded'      => $this->handleIntentSucceeded($event->data->object),
                'payment_intent.payment_failed' => $this->handleIntentFailed($event->data->object),
                'charge.refunded'               => $this->handleChargeRefunded($event->data->object),
                default                         => Log::debug("Stripe webhook ignored: {$event->type}"),
            };
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler threw', [
                'event'   => $event->type,
                'message' => $e->getMessage(),
            ]);
        }

        return response('OK', 200);
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

    // ─────────────────────────────────────────────────────────────────────────
    // Private webhook handlers
    // ─────────────────────────────────────────────────────────────────────────

    private function handleIntentSucceeded(\Stripe\PaymentIntent $intent): void
    {
        $order = $this->checkout->confirmPayment($intent->id);

        // Queue success notification
        try {
            if ($order->payment_status === 'paid') {
                $order->user->notify(new \App\Notifications\PaymentSuccessNotification($order));
            }
        } catch (\Throwable $e) {
            Log::error('PaymentSuccessNotification failed', ['order_id' => $order->id]);
        }
    }

    private function handleIntentFailed(\Stripe\PaymentIntent $intent): void
    {
        $this->checkout->handlePaymentFailed($intent->id);

        // Queue failure notification
        try {
            $payment = \App\Models\Payment::where('gateway_transaction_id', $intent->id)->first();
            if ($payment) {
                $order = Order::find($payment->order_id);
                $order?->user?->notify(new \App\Notifications\PaymentFailedNotification($order));
            }
        } catch (\Throwable $e) {
            Log::error('PaymentFailedNotification failed', ['intent_id' => $intent->id]);
        }
    }

    /**
     * charge.refunded — Stripe-initiated refund (from Dashboard or API).
     * Reconciles our payment/order records.
     */
    private function handleChargeRefunded(\Stripe\Charge $charge): void
    {
        if (empty($charge->payment_intent)) return;

        $payment = \App\Models\Payment::where('gateway_transaction_id', $charge->payment_intent)->first();
        if (! $payment) return;

        if ($payment->status !== 'refunded') {
            $refunds = $charge->refunds->data ?? [];
            $latest  = ! empty($refunds) ? end($refunds) : null;

            $payment->update([
                'status'             => 'refunded',
                'gateway_refund_id'  => $latest?->id,
            ]);

            // Move order to refunded status (if not already)
            $order = Order::find($payment->order_id);
            if ($order && ! $order->isRefunded()) {
                $order->update([
                    'status'         => Order::STATUS_REFUNDED,
                    'payment_status' => 'refunded',
                ]);

                \App\Models\OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status'   => Order::STATUS_REFUNDED,
                    'notes'    => 'Refund reconciled via charge.refunded webhook.',
                ]);
            }
        }
    }
}
