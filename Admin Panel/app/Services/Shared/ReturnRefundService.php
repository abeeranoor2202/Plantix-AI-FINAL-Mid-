<?php

namespace App\Services\Shared;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Notifications\ReturnStatusNotification;
use App\Services\Shared\StockService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Refund as StripeRefund;
use Stripe\Stripe;

class ReturnRefundService
{
    public function __construct(
        private readonly StockService $stock,
    ) {}

    /**
     * Customer submits a return request.
     *
     * Enforces the return window: the order must have been delivered within
     * the last PLANTIX_RETURN_WINDOW_DAYS days.
     *
     * @param  UploadedFile[]|null  $images  Raw uploaded files (not paths)
     */
    public function requestReturn(User $user, Order $order, array $data): ReturnRequest
    {
        // ── 1. Allow only delivered orders ───────────────────────────────────
        if ($order->status !== 'delivered') {
            throw ValidationException::withMessages([
                'order' => 'Return requests can only be submitted for delivered orders.',
            ]);
        }

        // ── 2. Enforce return window ──────────────────────────────────────────
        $windowDays = config('plantix.return_window_days', 7);
        $deliveredAt = $order->delivered_at ?? $order->updated_at;

        if ($deliveredAt->diffInDays(now()) > $windowDays) {
            throw ValidationException::withMessages([
                'order' => "Return window has expired. Returns are only accepted within {$windowDays} days of delivery.",
            ]);
        }

        // ── 3. Prevent duplicate return for the same order ────────────────────
        if ($order->returnRequest()->exists()) {
            throw new \RuntimeException('A return request already exists for this order.');
        }

        // ── 4. Store uploaded images securely (private disk) ─────────────────
        $storedPaths = null;
        if (! empty($data['images']) && is_array($data['images'])) {
            $storedPaths = [];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $mimeMap = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
            ];

            foreach ($data['images'] as $file) {
                if (! ($file instanceof UploadedFile) || ! $file->isValid()) {
                    continue;
                }

                $mime = $file->getMimeType();
                if (! in_array($mime, $allowedMimes, true)) {
                    throw ValidationException::withMessages([
                        'images' => "File type '{$mime}' is not allowed. Only JPEG, PNG, WebP, and GIF images are accepted.",
                    ]);
                }

                $ext  = $mimeMap[$mime];
                $name = Str::uuid() . '.' . $ext;
                Storage::disk('local')->putFileAs('return-images/' . $order->id, $file, $name);
                $storedPaths[] = 'return-images/' . $order->id . '/' . $name;
            }
        }

        // ── 5. Create the return request ──────────────────────────────────────
        $return = ReturnRequest::create([
            'order_id'         => $order->id,
            'user_id'          => $user->id,
            'return_reason_id' => $data['return_reason_id'] ?? null,
            'description'      => $data['description'] ?? null,
            'images'           => $storedPaths ? json_encode($storedPaths) : null,
            'status'           => 'pending',
        ]);

        return $return->fresh(['order', 'reason']);
    }

    /**
     * Admin approves a return.
     */
    public function approve(ReturnRequest $return, ?string $adminNotes = null): ReturnRequest
    {
        $return->update(['status' => 'approved', 'admin_notes' => $adminNotes]);

        // ── Restore stock for returned items ──────────────────────────────────
        try {
            $return->loadMissing('order.items.product');
            foreach ($return->order->items as $item) {
                if ($item->product) {
                    $this->stock->restoreStock(
                        product:     $item->product,
                        qty:         $item->quantity,
                        reason:      'return',
                        orderId:     $return->order_id,
                        returnId:    $return->id,
                        initiatedBy: null,
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::error('Stock restoration on return failed: ' . $e->getMessage(), ['return_id' => $return->id]);
        }

        try {
            $return->user->notify(new ReturnStatusNotification($return));
        } catch (\Throwable $e) {
            Log::error('Return approval notification failed: ' . $e->getMessage());
        }

        return $return->fresh();
    }

    /**
     * Admin rejects a return.
     */
    public function reject(ReturnRequest $return, string $adminNotes): ReturnRequest
    {
        $return->update(['status' => 'rejected', 'admin_notes' => $adminNotes]);

        try {
            $return->user->notify(new ReturnStatusNotification($return));
        } catch (\Throwable $e) {
            Log::error('Return rejection notification failed: ' . $e->getMessage());
        }

        return $return->fresh();
    }

    /**
     * Process a refund for an approved return.
     *
     * Handles:
     *  - Stripe refund (full or partial via Stripe API with idempotency key)
     *  - Wallet credit
     *  - Order/return status update
     *  - Stock already restored in approve() — do NOT restore again here
     *
     * @throws \DomainException if return is not in approved status
     * @throws \Stripe\Exception\ApiErrorException on Stripe API failure
     * @throws \Throwable
     */
    public function processRefund(ReturnRequest $return, array $data, User $processedBy): Refund
    {
        if ($return->status !== 'approved') {
            throw new \DomainException('Return must be approved before a refund can be issued.');
        }

        // Prevent duplicate refund
        if ($return->status === 'refunded') {
            throw new \DomainException('A refund has already been issued for this return.');
        }

        $amount = (float) $data['amount'];
        $method = $data['method']; // 'original_payment' | 'wallet' | 'bank_transfer'

        $stripeRefundId  = null;
        $transactionRef  = $data['transaction_ref'] ?? null;

        // ── Stripe API refund ─────────────────────────────────────────────────
        if ($method === 'original_payment') {
            $order = $return->order;

            $payment = Payment::where('order_id', $order->id)
                               ->where('gateway', 'stripe')
                               ->where('status', 'completed')
                               ->first();

            if (! $payment || ! $payment->gateway_transaction_id) {
                throw new \DomainException(
                    'No completed Stripe payment found for this order. Use wallet or bank transfer.'
                );
            }

            Stripe::setApiKey(config('services.stripe.secret'));

            $stripeRefund = StripeRefund::create(
                [
                    'payment_intent' => $payment->gateway_transaction_id,
                    'amount'         => (int) round($amount * 100), // cents
                    'reason'         => 'requested_by_customer',
                    'metadata'       => [
                        'return_id' => $return->id,
                        'order_id'  => $order->id,
                        'admin_id'  => $processedBy->id,
                    ],
                ],
                ['idempotency_key' => 'refund-return-' . $return->id]
            );

            $stripeRefundId = $stripeRefund->id;
            $transactionRef = $stripeRefundId;

            // Update the payment record
            $payment->update([
                'status'            => 'refunded',
                'gateway_refund_id' => $stripeRefundId,
            ]);
        }

        // ── Persist refund + update statuses inside a transaction ─────────────
        return DB::transaction(function () use (
            $return, $data, $amount, $method, $stripeRefundId, $transactionRef, $processedBy
        ) {
            $refund = Refund::create([
                'return_id'       => $return->id,
                'order_id'        => $return->order_id,
                'amount'          => $amount,
                'method'          => $method,
                'status'          => 'processed',
                'transaction_ref' => $transactionRef,
                'notes'           => $data['notes'] ?? null,
                'processed_at'    => now(),
                'processed_by'    => $processedBy->id,
            ]);

            // Move return → refunded
            $return->update(['status' => 'refunded']);

            // Move order → refunded
            $return->order->update([
                'status'         => Order::STATUS_REFUNDED,
                'payment_status' => 'refunded',
            ]);

            \App\Models\OrderStatusHistory::create([
                'order_id'   => $return->order_id,
                'status'     => Order::STATUS_REFUNDED,
                'notes'      => "Refund issued ({$method}): {$amount}. Ref: {$transactionRef}",
                'changed_by' => $processedBy->id,
            ]);

            // Wallet credit
            if ($method === 'wallet') {
                $return->user->increment('wallet_amount', $amount);
            }

            // Notify customer
            try {
                $return->user->notify(new ReturnStatusNotification($return->fresh()));
            } catch (\Throwable $e) {
                Log::error('Refund notification failed', ['return_id' => $return->id]);
            }

            return $refund;
        });
    }
}
