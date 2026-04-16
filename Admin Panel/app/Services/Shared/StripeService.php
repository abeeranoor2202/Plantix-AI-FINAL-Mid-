<?php

namespace App\Services\Shared;

use App\Models\Appointment;
use App\Models\Order;
use App\Models\StripeAccount;
use App\Models\User;
use Illuminate\Support\Str;
use Stripe\Checkout\Session;
use Stripe\Connect\Account;
use Stripe\Connect\AccountLink;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Transfer;
use Stripe\Stripe;
use Stripe\Webhook;

/**
 * StripeService
 *
 * Single point of contact for all Stripe operations in the marketplace.
 *
 * Responsibilities:
 *  - Create PaymentIntents and Checkout Sessions
 *  - Create Stripe Connect accounts and onboarding links
 *  - Create Transfers for vendor / expert payouts
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
     * Create a Checkout Session for an order and expose the underlying PaymentIntent.
     */
    public function createOrderCheckoutSession(Order $order, array $metadata = []): array
    {
        $session = Session::create([
            'mode' => 'payment',
            'success_url' => route('payment.success', ['order_id' => $order->id], true) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel', ['order_id' => $order->id], true),
            'client_reference_id' => (string) $order->id,
            'metadata' => array_merge([
                'payment_type' => 'product',
                'order_id' => (string) $order->id,
                'user_id' => (string) $order->user_id,
            ], $metadata),
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower(config('plantix.currency_code', 'pkr')),
                    'unit_amount' => $this->toCents((float) $order->total),
                    'product_data' => [
                        'name' => 'Order ' . $order->order_number,
                        'description' => 'Plantix AI marketplace purchase',
                    ],
                ],
            ]],
            'payment_intent_data' => [
                'metadata' => array_merge([
                    'payment_type' => 'product',
                    'order_id' => (string) $order->id,
                ], $metadata),
            ],
        ], ['idempotency_key' => 'order-checkout-' . $order->id]);

        $paymentIntent = null;
        if (! empty($session->payment_intent)) {
            $paymentIntent = PaymentIntent::retrieve($session->payment_intent);
        }

        return [
            'session'       => $session,
            'paymentIntent' => $paymentIntent,
            'client_secret' => $paymentIntent?->client_secret,
            'checkout_url'  => $session->url,
        ];
    }

    /**
     * Create a Checkout Session for an appointment booking.
     */
    public function createAppointmentCheckoutSession(Appointment $appointment, array $metadata = []): array
    {
        $session = Session::create([
            'mode' => 'payment',
            'success_url' => route('payment.success', ['appointment_id' => $appointment->id], true) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel', ['appointment_id' => $appointment->id], true),
            'client_reference_id' => 'appointment-' . $appointment->id,
            'metadata' => array_merge([
                'payment_type' => 'appointment',
                'appointment_id' => (string) $appointment->id,
                'user_id' => (string) $appointment->user_id,
                'expert_id' => (string) $appointment->expert_id,
            ], $metadata),
            'line_items' => [[
                'quantity' => 1,
                'price_data' => [
                    'currency' => strtolower(config('plantix.currency_code', 'pkr')),
                    'unit_amount' => $this->toCents((float) $appointment->fee),
                    'product_data' => [
                        'name' => 'Appointment #' . $appointment->id,
                        'description' => 'Expert consultation booking',
                    ],
                ],
            ]],
            'payment_intent_data' => [
                'metadata' => array_merge([
                    'payment_type' => 'appointment',
                    'appointment_id' => (string) $appointment->id,
                ], $metadata),
            ],
        ], ['idempotency_key' => 'appointment-checkout-' . $appointment->id]);

        $paymentIntent = null;
        if (! empty($session->payment_intent)) {
            $paymentIntent = PaymentIntent::retrieve($session->payment_intent);
        }

        return [
            'session'       => $session,
            'paymentIntent' => $paymentIntent,
            'client_secret' => $paymentIntent?->client_secret,
            'checkout_url'  => $session->url,
        ];
    }

    /**
     * Create or refresh a Stripe Connect account link for a connected seller.
     */
    public function createConnectAccountLink(string $stripeAccountId, string $refreshUrl, string $returnUrl): AccountLink
    {
        return AccountLink::create([
            'account' => $stripeAccountId,
            'refresh_url' => $refreshUrl,
            'return_url' => $returnUrl,
            'type' => 'account_onboarding',
        ]);
    }

    /**
     * Create a Stripe Connect Express account for a seller if one does not exist.
     */
    public function createConnectAccount(User $user, string $type = 'express', array $metadata = []): Account
    {
        return Account::create([
            'type' => $type,
            'country' => config('services.stripe.country', 'PK'),
            'email' => $user->email,
            'capabilities' => [
                'transfers' => ['requested' => true],
            ],
            'business_type' => 'individual',
            'metadata' => array_merge([
                'user_id' => (string) $user->id,
                'user_role' => (string) $user->role,
            ], $metadata),
        ]);
    }

    public function retrieveConnectAccount(string $stripeAccountId): Account
    {
        return Account::retrieve($stripeAccountId);
    }

    /**
     * Send platform funds to a connected account.
     */
    public function createTransfer(int $amountCents, string $currency, string $destinationAccountId, string $transferGroup, array $metadata = []): Transfer
    {
        return Transfer::create([
            'amount' => $amountCents,
            'currency' => strtolower($currency),
            'destination' => $destinationAccountId,
            'transfer_group' => $transferGroup,
            'metadata' => $metadata,
        ]);
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
