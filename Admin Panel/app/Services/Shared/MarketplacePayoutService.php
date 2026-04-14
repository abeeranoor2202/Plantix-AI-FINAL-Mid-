<?php

namespace App\Services\Shared;

use App\Mail\Expert\ExpertPayoutMail;
use App\Mail\Vendor\VendorPayoutMail;
use App\Models\Appointment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\StripeAccount;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MarketplacePayoutService
{
    public function __construct(private readonly StripeService $stripe)
    {
    }

    public function settleOrder(Order $order): Payout
    {
        $vendor = $order->vendor()->with('author')->firstOrFail();
        $vendorUser = $vendor->author;
        $payment = Payment::where('order_id', $order->id)->where('payment_type', 'product')->latest()->first();

        return $this->settle(
            payment: $payment,
            amount: (float) $order->total,
            commissionRate: (float) ($vendor->commission_rate ?: config('plantix.platform_commission_rate', config('plantix.admin_commission_rate', 0.10))),
            recipient: $vendorUser,
            recipientType: 'vendor',
            recipientModelId: $vendor->id,
            paymentType: 'product',
            metadata: [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'vendor_id' => $vendor->id,
            ],
        );
    }

    public function settleAppointment(Appointment $appointment): Payout
    {
        $expert = $appointment->expert()->with('user')->firstOrFail();
        $expertUser = $expert->user;
        $payment = Payment::where('appointment_id', $appointment->id)->where('payment_type', 'appointment')->latest()->first();

        return $this->settle(
            payment: $payment,
            amount: (float) $appointment->fee,
            commissionRate: (float) config('plantix.platform_commission_rate', config('plantix.admin_commission_rate', 0.10)),
            recipient: $expertUser,
            recipientType: 'expert',
            recipientModelId: $expert->id,
            paymentType: 'appointment',
            metadata: [
                'appointment_id' => $appointment->id,
                'expert_id' => $expert->id,
                'scheduled_at' => optional($appointment->scheduled_at)->toISOString(),
            ],
        );
    }

    private function settle(
        ?Payment $payment,
        float $amount,
        float $commissionRate,
        User $recipient,
        string $recipientType,
        int $recipientModelId,
        string $paymentType,
        array $metadata = [],
    ): Payout {
        $commission = round($amount * $commissionRate, 2);
        $netAmount = round(max(0, $amount - $commission), 2);

        if ($payment) {
            $payout = Payout::updateOrCreate(
                [
                    'payment_id' => $payment->id,
                    'payment_type' => $paymentType,
                ],
                [
                    $recipientType === 'vendor' ? 'vendor_id' : 'expert_id' => $recipientModelId,
                    'user_id' => $recipient->id,
                    'amount' => $amount,
                    'commission' => $commission,
                    'net_amount' => $netAmount,
                    'status' => 'pending',
                    'method' => 'stripe_connect',
                    'metadata' => $metadata,
                ]
            );
        } else {
            $payout = Payout::create([
                $recipientType === 'vendor' ? 'vendor_id' : 'expert_id' => $recipientModelId,
                'user_id' => $recipient->id,
                'payment_type' => $paymentType,
                'amount' => $amount,
                'commission' => $commission,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'method' => 'stripe_connect',
                'metadata' => $metadata,
            ]);
        }

        $stripeAccount = StripeAccount::where('user_id', $recipient->id)->first();
        $stripeAccountId = $stripeAccount?->stripe_account_id;

        if (empty($stripeAccountId)) {
            return $payout;
        }

        try {
            $transfer = $this->stripe->createTransfer(
                amountCents: $this->stripe->toCents($netAmount),
                currency: config('plantix.currency_code', 'PKR'),
                destinationAccountId: $stripeAccountId,
                transferGroup: $paymentType . ':' . ($payment?->gateway_transaction_id ?? Str::uuid()->toString()),
                metadata: array_merge($metadata, [
                    'recipient_user_id' => $recipient->id,
                    'recipient_type' => $recipientType,
                ])
            );

            $payout->update([
                'status' => 'paid',
                'stripe_transfer_id' => $transfer->id,
                'paid_at' => now(),
            ]);

            if ($payment) {
                $payment->update([
                    'stripe_transfer_id' => $transfer->id,
                    'platform_commission' => $commission,
                    'net_amount' => $netAmount,
                    'stripe_account_id' => $stripeAccountId,
                ]);
            }

            $mailable = $recipientType === 'vendor'
                ? new VendorPayoutMail($recipient, $amount, $commission, $netAmount, $metadata)
                : new ExpertPayoutMail($recipient, $amount, $commission, $netAmount, $metadata);

            if (! empty($recipient->email)) {
                Mail::to($recipient->email)->queue($mailable);
            }

            return $payout;
        } catch (\Throwable $e) {
            $payout->update([
                'status' => 'failed',
                'failed_at' => now(),
                'metadata' => array_merge($metadata, ['error' => $e->getMessage()]),
            ]);

            if ($payment) {
                $payment->update([
                    'platform_commission' => $commission,
                    'net_amount' => $netAmount,
                ]);
            }

            return $payout;
        }
    }
}