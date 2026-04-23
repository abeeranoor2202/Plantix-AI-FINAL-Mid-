<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CheckoutRequest;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Shared\CartCheckoutService;
use App\Services\Shared\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;

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
        private readonly StripeService $stripe,
    ) {}

    /**
     * Single payment entry path for both orders and appointments.
     *
     * Route params:
     *  - type=order with {order}
     *  - type=appointment with {id}
     */
    public function createCheckoutSession(Request $request, ?Order $order = null, ?int $id = null): RedirectResponse|JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $type = (string) $request->route('type', 'order');

        try {
            if ($type === 'appointment') {
                $appointmentId = (int) ($id ?? $request->route('id'));
                $appointment = Appointment::where('user_id', $user->id)->findOrFail($appointmentId);

                if ($appointment->status !== Appointment::STATUS_PENDING_PAYMENT) {
                    if ($appointment->payment_status === 'paid') {
                        return redirect()->route('payment.success', ['appointment_id' => $appointment->id])
                            ->with('success', 'Payment completed successfully');
                    }

                    return redirect()->route('payment.cancel', ['appointment_id' => $appointment->id])
                        ->with('error', 'Payment failed or cancelled');
                }

                $checkout = $this->stripe->createAppointmentCheckoutSession($appointment, [
                    'appointment_id' => (string) $appointment->id,
                ]);

                $checkoutUrl = (string) ($checkout['checkout_url'] ?? '');
                if ($checkoutUrl === '') {
                    throw new \RuntimeException('Stripe Checkout URL is missing for appointment payment.');
                }

                $intent = $checkout['paymentIntent'] ?? null;

                $appointment->update([
                    'stripe_payment_intent_id' => $intent?->id,
                    'stripe_payment_status'    => $intent?->status ?? $appointment->stripe_payment_status,
                ]);

                Payment::updateOrCreate(
                    [
                        'appointment_id' => $appointment->id,
                        'gateway' => 'stripe',
                    ],
                    [
                        'user_id'                  => $user->id,
                        'gateway_transaction_id'   => $intent?->id,
                        'stripe_session_id'        => $checkout['session']->id,
                        'stripe_payment_intent_id' => $intent?->id,
                        'payment_type'             => 'appointment',
                        'amount'                   => $appointment->fee,
                        'currency'                 => strtolower(config('plantix.currency_code', 'usd')),
                        'status'                   => 'pending',
                    ]
                );

                if ($request->expectsJson()) {
                    return response()->json([
                        'checkout_url' => $checkoutUrl,
                        'appointment_id' => $appointment->id,
                    ]);
                }

                return redirect()->away($checkoutUrl);
            }

            $order ??= Order::findOrFail((int) $request->route('order'));

            if ((int) $order->user_id !== (int) $user->id) {
                abort(403);
            }

            if (! $order->isPendingPayment()) {
                if ($order->payment_status === 'paid') {
                    return redirect()->route('payment.success', ['order_id' => $order->id])
                        ->with('success', 'Payment completed successfully');
                }

                return redirect()->route('payment.cancel', ['order_id' => $order->id])
                    ->with('error', 'Payment failed or cancelled');
            }

            $checkout = $this->stripe->createOrderCheckoutSession($order, [
                'order_number' => $order->order_number,
            ]);

            $checkoutUrl = (string) ($checkout['checkout_url'] ?? '');
            if ($checkoutUrl === '') {
                throw new \RuntimeException('Stripe Checkout URL is missing for order payment.');
            }

            $intent = $checkout['paymentIntent'] ?? null;
            $order->update([
                'payment_intent_id' => $intent?->id,
            ]);

            Payment::updateOrCreate(
                ['order_id' => $order->id, 'gateway' => 'stripe'],
                [
                    'user_id'                  => $order->user_id,
                    'gateway_transaction_id'   => $intent?->id,
                    'stripe_session_id'        => $checkout['session']->id,
                    'stripe_payment_intent_id' => $intent?->id,
                    'payment_type'             => 'product',
                    'amount'                   => $order->total,
                    'currency'                 => strtolower(config('plantix.currency_code', 'usd')),
                    'status'                   => 'pending',
                ]
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'checkout_url' => $checkoutUrl,
                    'order_id' => $order->id,
                ]);
            }

            return redirect()->away($checkoutUrl);
        } catch (\Throwable $e) {
            Log::error('createCheckoutSession error', [
                'type' => $type,
                'order_id' => $order?->id,
                'appointment_id' => $id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('payment.cancel', [
                'order_id' => $order?->id,
                'appointment_id' => $id,
            ])->with('error', 'Payment failed or cancelled');
        }
    }

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
                'checkout_url'      => $result['checkout_url'] ?? null,
                'order_id'          => $order->id,
                'payment_intent_id' => $order->payment_intent_id,
            ]);
        }

        $checkoutUrl = (string) ($result['checkout_url'] ?? '');
        if ($checkoutUrl === '') {
            return back()->withErrors(['checkout' => 'Unable to create Stripe Checkout session. Please try again.']);
        }

        return redirect()->away($checkoutUrl);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Stripe payment page (shows Stripe.js payment form)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Route: GET /checkout/pay/{order}
     */
    public function showPaymentPage(Order $order): RedirectResponse|JsonResponse
    {
        if (! config('payment.manual_payment_enabled')) {
            Log::warning('Manual payment attempted while disabled', [
                'route' => 'checkout.pay',
                'order_id' => $order->id,
                'user_id' => Auth::id(),
            ]);

            abort(404);
        }

        if ((int) $order->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (! $order->isPendingPayment()) {
            if ($order->payment_status === 'paid') {
                return redirect()->route('order.success', $order->id);
            }

            return redirect()->route('order.details', ['id' => $order->id])
                ->withErrors(['order' => 'This order cannot be paid in its current state.']);
        }

        $order->loadMissing('items.product');

        return response()->view('frontend.checkout.stripe-payment', compact('order'));
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
        if (! config('payment.manual_payment_enabled')) {
            Log::warning('Manual payment attempted while disabled', [
                'route' => 'checkout.pay.confirm',
                'order_id' => $order->id,
                'user_id' => Auth::id(),
            ]);

            abort(404);
        }

        return redirect()->route('checkout.stripe.pay', $order->id);
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
        if (! session()->has('success')) {
            session()->flash('success', 'Payment completed successfully');
        }

        $sessionId = (string) $request->query('session_id', '');

        $appointmentId = $request->query('appointment_id');
        if ($appointmentId) {
            return redirect()->route('appointment.details', (int) $appointmentId)
                ->with('success', 'Payment completed successfully');
        }

        $orderId = $request->query('order_id') ?? session('pending_order_id');
        $order   = $orderId ? Order::find($orderId) : null;

        // Fallback reconciliation for cases where Stripe webhook is delayed/missed:
        // verify Checkout Session on return and finalize payment server-side.
        if ($order
            && (int) $order->user_id === (int) Auth::id()
            && $order->payment_method === 'stripe'
            && $order->payment_status !== 'paid'
            && $sessionId !== '') {
            try {
                Stripe::setApiKey(config('services.stripe.secret'));
                $session = StripeCheckoutSession::retrieve($sessionId);

                $paymentIntentId = (string) ($session->payment_intent ?? '');
                $isSessionPaid = in_array((string) ($session->payment_status ?? ''), ['paid', 'no_payment_required'], true);

                if ($isSessionPaid && $paymentIntentId !== '') {
                    Payment::updateOrCreate(
                        ['order_id' => $order->id, 'gateway' => 'stripe'],
                        [
                            'user_id'                  => $order->user_id,
                            'gateway_transaction_id'   => $paymentIntentId,
                            'stripe_session_id'        => $sessionId,
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'payment_type'             => 'product',
                            'amount'                   => $order->total,
                            'currency'                 => strtolower(config('plantix.currency_code', 'usd')),
                            'status'                   => 'pending',
                        ]
                    );

                    $this->checkout->confirmPayment($paymentIntentId);
                    $order = $order->fresh();
                }
            } catch (\Throwable $e) {
                Log::warning('Stripe success fallback reconciliation failed', [
                    'order_id' => $order->id,
                    'session_id' => $sessionId,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        if ($order && (int) $order->user_id === (int) Auth::id() && $order->payment_status === 'paid') {
            return view('customer.payment-success', compact('order'));
        }

        return redirect()->route('orders')->with('success', 'Payment completed successfully');
    }

    public function cancel(Request $request): RedirectResponse
    {
        $appointmentId = (int) $request->query('appointment_id', 0);
        if ($appointmentId > 0) {
            return redirect()->route('appointment.details', $appointmentId)
                ->with('error', 'Payment failed or cancelled');
        }

        $orderId = (int) $request->query('order_id', 0);
        if ($orderId > 0) {
            return redirect()->route('order.details', ['id' => $orderId])
                ->with('error', 'Payment failed or cancelled');
        }

        return redirect()->route('checkout')
            ->with('error', 'Payment failed or cancelled');
    }

}
