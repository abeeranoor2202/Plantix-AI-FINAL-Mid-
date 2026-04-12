<?php

namespace App\Services\Shared;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Refund as StripeRefund;
use Stripe\Stripe;

class RefundService
{
    public function __construct(
        private readonly WalletService $wallet,
        private readonly ReturnService $returns,
    ) {}

    public function calculateAmount(ReturnRequest $return): float
    {
        $return->loadMissing('order.items', 'items');

        $subtotal = (float) $return->order->subtotal;
        $discount = (float) $return->order->discount_amount;
        $tax = (float) $return->order->tax_amount;

        $gross = 0.0;
        foreach ($return->items as $returnItem) {
            $orderItem = $return->order->items->firstWhere('product_id', $returnItem->product_id);
            if (! $orderItem) {
                continue;
            }

            $gross += (float) $orderItem->unit_price * (int) $returnItem->quantity;
        }

        if ($gross <= 0) {
            return 0.0;
        }

        $ratio = $subtotal > 0 ? min(1, $gross / $subtotal) : 1;
        $allocatedDiscount = $discount * $ratio;
        $allocatedTax = $tax * $ratio;

        return max(0, round($gross - $allocatedDiscount + $allocatedTax, 2));
    }

    public function process(ReturnRequest $return, array $data, User $processedBy): Refund
    {
        $return->loadMissing('order.items.product', 'user');

        if ($return->status !== 'approved') {
            throw new \DomainException('Return must be approved before a refund can be issued.');
        }

        if ($return->refund()->exists()) {
            throw new \DomainException('A refund has already been created for this return.');
        }

        if (! $this->returns->orderIsRefundable($return->order)) {
            throw new \DomainException('Refund not allowed for this product.');
        }

        $amount = (float) ($data['amount'] ?? $this->calculateAmount($return));
        $method = $this->normalizeMethod($data['method'] ?? 'manual');
        $transactionRef = $data['transaction_ref'] ?? null;

        $this->returns->markRefundProcessing($return, $processedBy);

        return DB::transaction(function () use ($return, $processedBy, $amount, $method, $transactionRef, $data) {
            $refund = Refund::create([
                'return_id'       => $return->id,
                'order_id'        => $return->order_id,
                'amount'          => $amount,
                'method'          => $method,
                'status'          => 'pending',
                'transaction_ref' => $transactionRef,
                'notes'           => $data['notes'] ?? null,
                'processed_by'    => $processedBy->id,
            ]);

            $gatewayRef = null;

            try {
                if ($method === 'wallet') {
                    $this->wallet->creditUser(
                        $return->user,
                        $amount,
                        "Refund for Order #{$return->order->order_number}",
                        $return->order
                    );
                    $gatewayRef = 'wallet-credit-' . $refund->id;
                } elseif ($method === 'original') {
                    $gatewayRef = $this->refundOriginalPayment($return, $amount, $processedBy, $refund->id);
                } else {
                    $gatewayRef = $transactionRef ?: 'manual-refund-' . $refund->id;
                }

                $refund->update([
                    'status'          => 'processed',
                    'transaction_ref'  => $gatewayRef,
                    'processed_at'     => now(),
                ]);

                $this->returns->complete($return, 'Refund processed successfully.', $processedBy);
                $return->order->update(['status' => Order::STATUS_REFUNDED, 'payment_status' => 'refunded']);

                return $refund->fresh();
            } catch (\Throwable $e) {
                $refund->update([
                    'status' => 'failed',
                    'notes'  => trim(($refund->notes ? $refund->notes . ' ' : '') . $e->getMessage()),
                ]);

                $return->update(['status' => 'approved']);
                $return->order->update(['status' => Order::STATUS_RETURNED]);

                Log::error('Return refund processing failed', [
                    'return_id' => $return->id,
                    'refund_id' => $refund->id,
                    'error'     => $e->getMessage(),
                ]);

                throw $e;
            }
        });
    }

    private function normalizeMethod(string $method): string
    {
        return match ($method) {
            'original_payment', 'original' => 'original',
            'bank_transfer', 'manual'      => 'manual',
            default                        => 'wallet',
        };
    }

    private function refundOriginalPayment(ReturnRequest $return, float $amount, User $processedBy, int $refundId): string
    {
        $payment = Payment::where('order_id', $return->order->id)
            ->where('gateway', 'stripe')
            ->where('status', 'completed')
            ->first();

        if (! $payment || ! $payment->gateway_transaction_id) {
            throw new \DomainException('No completed Stripe payment found for this order. Use wallet or manual refund.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $stripeRefund = StripeRefund::create([
            'payment_intent' => $payment->gateway_transaction_id,
            'amount'         => (int) round($amount * 100),
            'reason'         => 'requested_by_customer',
            'metadata'       => [
                'return_id' => $return->id,
                'order_id'  => $return->order->id,
                'admin_id'  => $processedBy->id,
                'refund_id' => $refundId,
            ],
        ], [
            'idempotency_key' => 'refund-return-' . $return->id,
        ]);

        $payment->update([
            'status'            => 'refunded',
            'gateway_refund_id' => $stripeRefund->id,
        ]);

        return $stripeRefund->id;
    }
}