<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\Shared\AppointmentService;
use App\Services\Shared\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
 *  - AppointmentService methods are idempotent; safe for Stripe retries
 *  - Logs duplicates but returns 200 to prevent Stripe from retrying endlessly
 *
 * Supported events:
 *  - payment_intent.succeeded      → status: pending_expert_approval, notify expert
 *  - payment_intent.payment_failed → status: payment_failed, release slot
 *  - charge.refunded               → mark is_refunded, notify customer
 */
class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripeService       $stripe,
        private readonly AppointmentService  $service,
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

        // ── 2. Route to handler ───────────────────────────────────────────────
        try {
            match ($event->type) {
                'payment_intent.succeeded'      => $this->onPaymentIntentSucceeded($event->data->object),
                'payment_intent.payment_failed' => $this->onPaymentIntentFailed($event->data->object),
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
        }

        // Always return 200 after processing (Stripe retries on non-2xx)
        return response('Webhook received', 200);
    }

    // ── Event handlers ────────────────────────────────────────────────────────

    private function onPaymentIntentSucceeded(object $pi): void
    {
        $this->service->confirmPayment($pi->id, 'succeeded');
    }

    private function onPaymentIntentFailed(object $pi): void
    {
        $this->service->handlePaymentFailed($pi->id);
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
