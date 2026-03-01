<?php

namespace App\Services\Shared;

use App\Models\Appointment;
use Illuminate\Support\Str;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * StripeService
 *
 * Single point of contact for all Stripe operations in the appointment module.
 *
 * Responsibilities:
 *  - Create PaymentIntents (prepaid booking requirement)
 *  - Verify webhook signatures (prevent replay attacks)
 *  - Issue full and partial refunds
 *  - Retrieve PaymentIntent status (reconciliation)
 *
 * Security:
 *  - Stripe secret NEVER leaves this class; read from env only
 *  - Webhook signature verified on every inbound request
 *  - Idempotency keys prevent double-charge on retry
 *  - Raw gateway response stored (PII-safe fields only)
 */
class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        Stripe::setApiVersion('2024-04-10'); // pin to a stable version
    }

    // ── PaymentIntent ─────────────────────────────────────────────────────────

    /**
     * Create a Stripe PaymentIntent for an appointment.
     *
     * Returns the PI object. The client_secret is sent to the frontend
     * to complete payment via Stripe.js — we NEVER handle raw card data.
     *
     * Idempotency key: deterministic per appointment so retries are safe.
     *
     * @throws ApiErrorException
     */
    public function createPaymentIntent(Appointment $appointment): PaymentIntent
    {
        $idempotencyKey = $this->idempotencyKey($appointment);

        $pi = PaymentIntent::create(
            [
                'amount'      => $this->toCents($appointment->fee),
                'currency'    => strtolower(config('plantix.currency_code', 'pkr')),
                'metadata'    => [
                    'appointment_id' => $appointment->id,
                    'customer_id'    => $appointment->user_id,
                    'expert_id'      => $appointment->expert_id,
                ],
                'description' => "Appointment #{$appointment->id} — Plantix AI",
                // Automatic payment methods: let Stripe decide what's available
                'automatic_payment_methods' => ['enabled' => true],
            ],
            ['idempotency_key' => $idempotencyKey]
        );

        // Persist PI ID and idempotency key immediately to prevent duplicate PIs
        $appointment->updateQuietly([
            'stripe_payment_intent_id' => $pi->id,
            'stripe_payment_status'    => $pi->status,
            'payment_idempotency_key'  => $idempotencyKey,
            'status'                   => Appointment::STATUS_PENDING_PAYMENT,
        ]);

        return $pi;
    }

    /**
     * Retrieve an existing PaymentIntent from Stripe.
     * Used for reconciliation (e.g. user closed tab before redirect).
     *
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    /**
     * Issue a full refund on an appointment's PaymentIntent.
     *
     * @throws ApiErrorException|\DomainException
     */
    public function refundFull(Appointment $appointment, ?string $adminNote = null): Refund
    {
        $this->assertRefundable($appointment);

        $refund = Refund::create(
            [
                'payment_intent' => $appointment->stripe_payment_intent_id,
                'metadata'       => [
                    'appointment_id' => $appointment->id,
                    'admin_note'     => $adminNote ?? '',
                ],
            ],
            ['idempotency_key' => "refund-full-{$appointment->id}"]
        );

        $this->applyRefundToAppointment($appointment, $refund, $appointment->fee);

        return $refund;
    }

    /**
     * Issue a partial refund.
     *
     * @throws ApiErrorException|\DomainException
     */
    public function refundPartial(Appointment $appointment, float $amount, ?string $adminNote = null): Refund
    {
        $this->assertRefundable($appointment);

        if ($amount <= 0 || $amount > $appointment->fee) {
            throw new \DomainException('Refund amount must be between 0 and the original fee.');
        }

        $refund = Refund::create(
            [
                'payment_intent' => $appointment->stripe_payment_intent_id,
                'amount'         => $this->toCents($amount),
                'metadata'       => [
                    'appointment_id' => $appointment->id,
                    'admin_note'     => $adminNote ?? '',
                ],
            ],
            ['idempotency_key' => "refund-partial-{$appointment->id}-" . $this->toCents($amount)]
        );

        $this->applyRefundToAppointment($appointment, $refund, $amount);

        return $refund;
    }

    // ── Webhook ───────────────────────────────────────────────────────────────

    /**
     * Verify the Stripe webhook signature and construct the Event.
     *
     * MUST be called before processing any webhook payload.
     * Throws SignatureVerificationException on tampered/replayed payloads.
     *
     * @throws SignatureVerificationException
     */
    public function constructWebhookEvent(string $payload, string $sigHeader): \Stripe\Event
    {
        $secret = config('services.stripe.webhook_secret');

        if (empty($secret)) {
            throw new \RuntimeException('STRIPE_WEBHOOK_SECRET is not configured.');
        }

        // Stripe's SDK handles timestamp tolerance (default ±300 s)
        return Webhook::constructEvent($payload, $sigHeader, $secret);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Convert a decimal amount (PKR/USD) to Stripe's integer cents representation.
     * For zero-decimal currencies this would be just (int)$amount, but for safety
     * we always multiply by 100 — adjust per currency if needed.
     */
    public function toCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Deterministic idempotency key per appointment.
     * Stable across retries; changes only when a new PI is explicitly needed.
     */
    public function idempotencyKey(Appointment $appointment): string
    {
        // Include appointment ID + a hash of the fee so that if an admin changes
        // the fee before payment, a new PI is created rather than reusing the old one.
        return 'appt-pi-' . $appointment->id . '-' . substr(
            hash('sha256', $appointment->id . '|' . $appointment->fee),
            0,
            16
        );
    }

    private function assertRefundable(Appointment $appointment): void
    {
        if (empty($appointment->stripe_payment_intent_id)) {
            throw new \DomainException('No Stripe PaymentIntent associated with this appointment.');
        }

        if ($appointment->is_refunded) {
            throw new \DomainException('This appointment has already been fully refunded.');
        }

        if ($appointment->stripe_payment_status !== 'succeeded') {
            throw new \DomainException('Payment has not succeeded; nothing to refund.');
        }
    }

    private function applyRefundToAppointment(Appointment $appointment, Refund $refund, float $amount): void
    {
        $appointment->update([
            'is_refunded'      => true,
            'refunded_at'      => now(),
            'stripe_refund_id' => $refund->id,
            'refund_amount'    => $amount,
            'payment_status'   => 'refunded',
        ]);
    }
}
