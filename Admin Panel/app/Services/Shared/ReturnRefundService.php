<?php

namespace App\Services\Shared;

use App\Models\Order;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReturnRefundService
{
    public function __construct(
        private readonly ReturnService $returns,
        private readonly RefundService $refunds,
    ) {}

    public function orderIsReturnable(Order $order): bool
    {
        return $this->returns->orderIsReturnable($order);
    }

    public function orderIsRefundable(Order $order): bool
    {
        return $this->returns->orderIsRefundable($order);
    }

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
        return $this->returns->createReturn($user, $order, $data);
    }

    /**
     * Admin approves a return.
     */
    public function approve(ReturnRequest $return, ?string $adminNotes = null): ReturnRequest
    {
        return $this->returns->approve($return, $adminNotes);
    }

    /**
     * Admin rejects a return.
     */
    public function reject(ReturnRequest $return, string $adminNotes): ReturnRequest
    {
        return $this->returns->reject($return, $adminNotes);
    }

    public function forceApprove(ReturnRequest $return, ?string $adminNotes = null, ?User $actor = null): ReturnRequest
    {
        return $this->returns->forceStatus($return, 'approved', $adminNotes, $actor);
    }

    public function forceReject(ReturnRequest $return, string $adminNotes, ?User $actor = null): ReturnRequest
    {
        return $this->returns->forceStatus($return, 'rejected', $adminNotes, $actor);
    }

    public function complete(ReturnRequest $return, ?string $adminNotes = null, ?User $actor = null): ReturnRequest
    {
        return $this->returns->complete($return, $adminNotes, $actor);
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
        return $this->refunds->process($return, $data, $processedBy);
    }

    private function normalizeReturnData(array $data): array
    {
        return [
            'reason_id' => $data['reason_id'] ?? $data['return_reason_id'] ?? null,
            'notes'     => $data['notes'] ?? $data['description'] ?? null,
            'items'     => $data['items'] ?? [],
        ];
    }
}
