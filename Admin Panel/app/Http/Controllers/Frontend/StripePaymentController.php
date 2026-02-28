<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /payments/stripe/intent
    // Creates a PaymentIntent and returns the client_secret to the frontend.
    // ──────────────────────────────────────────────────────────────────────────
    public function createIntent(Request $request): JsonResponse
    {
        $request->validate(['order_id' => 'required|integer|exists:orders,id']);

        /** @var \App\Models\User $user */
        $user  = Auth::user();
        $order = Order::findOrFail($request->order_id);

        // Guard: the order must belong to this user and still be awaiting payment
        if ((int) $order->user_id !== (int) $user->id) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['error' => 'Order is already paid.'], 422);
        }

        try {
            // Re-use an existing pending payment record if present
            $payment = Payment::where('order_id', $order->id)
                              ->where('status', 'pending')
                              ->where('gateway', 'stripe')
                              ->first();

            if ($payment && $payment->gateway_transaction_id) {
                // Retrieve the existing PaymentIntent to avoid creating duplicates
                $intent = PaymentIntent::retrieve($payment->gateway_transaction_id);
            } else {
                $intent = PaymentIntent::create([
                    'amount'   => (int) round($order->total * 100), // pence / cents
                    'currency' => strtolower(config('plantix.currency', 'usd')),
                    'metadata' => [
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'user_id'      => $user->id,
                    ],
                ], ['idempotency_key' => 'order_intent_' . $order->id]);

                Payment::updateOrCreate(
                    ['order_id' => $order->id, 'gateway' => 'stripe'],
                    [
                        'user_id'                => $user->id,
                        'gateway_transaction_id' => $intent->id,
                        'amount'                 => $order->total,
                        'currency'               => strtolower(config('plantix.currency', 'usd')),
                        'status'                 => 'pending',
                    ]
                );
            }

            return response()->json([
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe createIntent error', [
                'order_id' => $order->id,
                'message'  => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Payment service unavailable. Please try again.'], 503);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // POST /stripe/webhook  (no CSRF; verified by Stripe signature)
    // ──────────────────────────────────────────────────────────────────────────
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook: invalid payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook: invalid signature', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook event received', ['type' => $event->type]);

        match ($event->type) {
            'payment_intent.succeeded'              => $this->handleIntentSucceeded($event->data->object),
            'payment_intent.payment_failed'         => $this->handleIntentFailed($event->data->object),
            'charge.refunded'                       => $this->handleChargeRefunded($event->data->object),
            default                                 => null,
        };

        return response('OK', 200);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // GET /payment/success
    // Redirect landing page after Stripe confirms payment on the frontend.
    // ──────────────────────────────────────────────────────────────────────────
    public function success(Request $request)
    {
        $orderId = $request->query('order_id');
        $order   = $orderId ? Order::find($orderId) : null;

        // If order belongs to authenticated user and is now paid, show success view.
        if ($order && (int) $order->user_id === (int) Auth::id() && $order->payment_status === 'paid') {
            return view('customer.payment-success', compact('order'));
        }

        return redirect()->route('customer.orders.index')->with('info', 'Payment is being processed.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function handleIntentSucceeded(\Stripe\PaymentIntent $intent): void
    {
        DB::transaction(function () use ($intent) {
            $payment = Payment::where('gateway_transaction_id', $intent->id)
                              ->lockForUpdate()
                              ->first();

            if (! $payment) {
                Log::error('Stripe webhook: no Payment record for intent', ['intent_id' => $intent->id]);
                return;
            }

            if ($payment->status === 'completed') {
                return; // Idempotent — already processed
            }

            $payment->update([
                'status'           => 'completed',
                'gateway_response' => $intent->toArray(),
                'paid_at'          => now(),
            ]);

            // Mark order as paid and move status to confirmed
            $order = Order::lockForUpdate()->find($payment->order_id);
            if ($order && $order->payment_status !== 'paid') {
                $order->update([
                    'payment_status' => 'paid',
                    'status'         => 'confirmed',
                ]);
            }
        });
    }

    private function handleIntentFailed(\Stripe\PaymentIntent $intent): void
    {
        $payment = Payment::where('gateway_transaction_id', $intent->id)->first();
        if (! $payment) {
            return;
        }

        $payment->update([
            'status'           => 'failed',
            'gateway_response' => $intent->toArray(),
        ]);

        Order::where('id', $payment->order_id)
             ->where('payment_status', 'pending')
             ->update(['payment_status' => 'failed']);
    }

    private function handleChargeRefunded(\Stripe\Charge $charge): void
    {
        if (empty($charge->payment_intent)) {
            return;
        }

        $payment = Payment::where('gateway_transaction_id', $charge->payment_intent)->first();
        if (! $payment) {
            return;
        }

        $payment->update([
            'status'           => 'refunded',
            'gateway_response' => $charge->toArray(),
        ]);

        Order::where('id', $payment->order_id)
             ->update(['payment_status' => 'refunded']);
    }
}
