<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\StripeWebhookEvent;
use App\Services\Shared\AppointmentService;
use App\Services\Shared\CartCheckoutService;
use App\Services\Shared\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

/**
 * StripeWebhookController
 *
 * Handles inbound Stripe webhook events for the appointment payment lifecycle.
 *
 * Security:
 *  - Signature verified via STRIPE_WEBHOOK_SECRET on every request
 *  - Raw payload read BEFORE any framework input parsing (critical for sig check)
 *  - Route is excluded from CSRF middleware in Kernel.php
 *  - Rate-limited at nginx/server level (not application level)
 *
 * Idempotency:
 *  - Stores Stripe event IDs in stripe_webhook_events
 *  - Duplicate event_id deliveries are acknowledged and ignored
 *
 * Supported events:
 *  - payment_intent.succeeded      → mark payment complete (order paid / appointment confirmed)
 *  - payment_intent.payment_failed → status: payment_failed, release slot
 *  - charge.refunded               → mark is_refunded, notify customer
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripeService       $stripe,
        private readonly AppointmentService  $service,
        private readonly CartCheckoutService $checkout,
    ) {}

    /**
     * Entry point — all Stripe webhook events arrive here.
     * Route: POST /stripe/webhook  (no auth, no CSRF)
     */
    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        // ── 1. Verify signature ───────────────────────────────────────────────
        try {
            $event = $this->stripe->constructWebhookEvent($payload, $sigHeader);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed: ' . $e->getMessage(), [
                'ip' => $request->ip(),
            ]);
            return response('Signature verification failed', 400);
        } catch (\RuntimeException $e) {
            Log::error('Stripe webhook configuration error: ' . $e->getMessage());
            return response('Webhook configuration error', 500);
        }

        Log::info('Stripe webhook received', ['type' => $event->type, 'id' => $event->id]);

        // ── 2. Enforce event-id idempotency ─────────────────────────────────
        $eventId = (string) ($event->id ?? '');
        if ($eventId === '') {
            Log::warning('Stripe webhook event without id was ignored.', ['type' => $event->type]);
            return response('Webhook received', 200);
        }

        try {
            $record = StripeWebhookEvent::create([
                'provider'      => 'stripe',
                'event_id'      => $eventId,
                'event_type'    => (string) $event->type,
                'payload_hash'  => hash('sha256', $payload),
                'payload'       => json_decode($payload, true),
                'processed_at'  => null,
            ]);
        } catch (QueryException $e) {
            // Duplicate-key SQLSTATEs: 23000 (MySQL/SQLite), 23505 (Postgres).
            if (in_array(($e->errorInfo[0] ?? null), ['23000', '23505'], true)) {
                Log::info('Stripe webhook duplicate ignored', ['event_id' => $eventId, 'type' => $event->type]);
                return response('Webhook received', 200);
            }

            throw $e;
        }

        // ── 3. Route to handler ───────────────────────────────────────────────
        try {
            match ($event->type) {
                'payment_intent.succeeded'      => $this->onPaymentIntentSucceeded($event->data->object),
                'payment_intent.payment_failed' => $this->onPaymentIntentFailed($event->data->object),
                'checkout.session.completed'    => $this->onCheckoutSessionCompleted($event->data->object),
                'charge.refunded'               => $this->onChargeRefunded($event->data->object),
                default                         => Log::debug("Stripe webhook ignored: {$event->type}"),
            };
        } catch (\Throwable $e) {
            // Log but return 200 so Stripe doesn't keep retrying indefinitely.
            // Investigate via appointment_logs + Stripe dashboard.
            Log::error("Stripe webhook handler threw: {$e->getMessage()}", [
                'event_type' => $event->type,
                'event_id'   => $event->id,
                'trace'      => $e->getTraceAsString(),
            ]);
        } finally {
            $record->forceFill(['processed_at' => now()])->save();
        }

        // Always return 200 after processing (Stripe retries on non-2xx)
        return response('Webhook received', 200);
    }

    // ── Event handlers ────────────────────────────────────────────────────────

    private function onPaymentIntentSucceeded(object $pi): void
    {
        $paymentType = (string) ($pi->metadata->payment_type ?? 'appointment');

        if ($paymentType === 'product') {
            $this->checkout->confirmPayment($pi->id);
            return;
        }

        $this->service->confirmPayment($pi->id, 'succeeded');
    }
    private function onPaymentIntentFailed(object $pi): void
    {
        $paymentType = (string) ($pi->metadata->payment_type ?? 'appointment');

        if ($paymentType === 'product') {
            $this->checkout->handlePaymentFailed($pi->id);
            return;
        }

        $this->service->handlePaymentFailed($pi->id);
    }

    private function onCheckoutSessionCompleted(object $session): void
    {
        $paymentIntentId = (string) ($session->payment_intent ?? '');
        $paymentType = (string) (($session->metadata->payment_type ?? null) ?? '');
        $sessionId = (string) ($session->id ?? '');

        if ($paymentIntentId === '') {
            Log::warning('checkout.session.completed without payment_intent', [
                'session_id' => $session->id ?? null,
            ]);
            return;
        }

        if ($paymentType === 'product') {
            $this->checkout->confirmPayment($paymentIntentId);
            return;
        }

        // Pass session ID so confirmPayment can find the appointment even when
        // stripe_payment_intent_id was null at booking time (Checkout Session flow).
        $this->service->confirmPayment($paymentIntentId, 'succeeded', $sessionId);
    }

    /**
     * charge.refunded is fired when a refund is created via Stripe Dashboard
     * or programmatically.  We reconcile our records here.
     */
    private function onChargeRefunded(object $charge): void
    {
        $piId = $charge->payment_intent ?? null;

        if (! $piId) {
            return;
        }

        $appointment = Appointment::where('stripe_payment_intent_id', $piId)->first();

        if (! $appointment) {
            Log::warning("Stripe charge.refunded: no appointment found for PI {$piId}.");
            return;
        }

        // Reconcile if the refund was issued from Stripe Dashboard (not our code)
        if (! $appointment->is_refunded) {
            $refunds = $charge->refunds->data ?? [];
            $latestRefund = ! empty($refunds) ? end($refunds) : null;

            $appointment->update([
                'is_refunded'      => true,
                'refunded_at'      => now(),
                'stripe_refund_id' => $latestRefund?->id,
                'refund_amount'    => $latestRefund ? ($latestRefund->amount / 100) : $appointment->fee,
                'payment_status'   => 'refunded',
            ]);

            \App\Models\AppointmentLog::record(
                $appointment,
                'refund_reconciled_from_webhook',
                null,
                null,
                null,
                'Refund detected via charge.refunded webhook.'
            );

            Log::info("Appointment #{$appointment->id} refund reconciled via webhook.");
        }
    }
}
