<?php

namespace App\Services\Shared;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\ReturnItem;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Notifications\ReturnLifecycleNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ReturnService
{
    public function __construct(
        private readonly StockService $stock,
    ) {}

    public function orderIsReturnable(Order $order): bool
    {
        $order->loadMissing('items.product');

        if ($order->status !== Order::STATUS_DELIVERED) {
            return false;
        }

        $windowDays = (int) config('plantix.return_window_days', 7);
        $deliveredAt = $order->delivered_at ?? $order->updated_at;

        if (! $deliveredAt || $deliveredAt->diffInDays(now()) > $windowDays) {
            return false;
        }

        return $order->items->contains(function ($item) {
            return $item->product?->is_returnable && $item->remainingReturnQuantity() > 0;
        });
    }

    public function orderIsRefundable(Order $order): bool
    {
        $order->loadMissing('items.product');

        return $order->items->contains(function ($item) {
            return $item->product?->is_refundable && $item->remainingReturnQuantity() > 0;
        });
    }

    public function createReturn(User $user, Order $order, array $data): ReturnRequest
    {
        if ($order->status !== Order::STATUS_DELIVERED) {
            throw ValidationException::withMessages([
                'order' => 'Return requests can only be submitted for delivered orders.',
            ]);
        }

        if (! $this->orderIsReturnable($order)) {
            throw ValidationException::withMessages([
                'order' => 'This order is not eligible for return.',
            ]);
        }

        if ($order->returnRequest()->whereIn('status', ['pending', 'approved', 'refund_processing', 'completed'])->exists()) {
            throw ValidationException::withMessages([
                'order' => 'A return request already exists for this order.',
            ]);
        }

        $normalizedItems = $this->normalizeItems($order, (array) ($data['items'] ?? []));
        if ($normalizedItems === []) {
            throw ValidationException::withMessages([
                'items' => 'Please select at least one item and quantity to return.',
            ]);
        }

        return DB::transaction(function () use ($user, $order, $data, $normalizedItems) {
            $return = ReturnRequest::create([
                'order_id'     => $order->id,
                'user_id'      => $user->id,
                'reason_id'    => $data['reason_id'] ?? $data['return_reason_id'] ?? null,
                'notes'        => $data['notes'] ?? $data['description'] ?? null,
                'status'       => 'pending',
                'requested_at' => now(),
            ]);

            foreach ($normalizedItems as $item) {
                ReturnItem::create([
                    'return_id'  => $return->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }

            $order->update(['status' => Order::STATUS_RETURN_REQUESTED]);
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => Order::STATUS_RETURN_REQUESTED,
                'notes'      => 'Return request submitted by customer.',
                'changed_by' => $user->id,
            ]);

            $this->notifyParties($return->fresh(['order', 'user', 'reason', 'items.product']), 'requested');

            return $return->fresh(['order', 'user', 'reason', 'items.product']);
        });
    }

    public function approve(ReturnRequest $return, ?string $adminNotes = null, ?User $actor = null): ReturnRequest
    {
        return $this->transition($return, 'approved', $adminNotes, $actor);
    }

    public function reject(ReturnRequest $return, string $adminNotes, ?User $actor = null): ReturnRequest
    {
        return $this->transition($return, 'rejected', $adminNotes, $actor);
    }

    public function forceStatus(ReturnRequest $return, string $status, ?string $adminNotes = null, ?User $actor = null): ReturnRequest
    {
        return $this->transition($return, $status, $adminNotes, $actor, true);
    }

    public function markRefundProcessing(ReturnRequest $return, ?User $actor = null): ReturnRequest
    {
        return $this->transition($return, 'refund_processing', null, $actor);
    }

    public function complete(ReturnRequest $return, ?string $adminNotes = null, ?User $actor = null): ReturnRequest
    {
        return $this->transition($return, 'completed', $adminNotes, $actor);
    }

    public function normalizeItems(Order $order, array $items): array
    {
        $order->loadMissing('items.product');

        $requested = [];
        foreach ($items as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity  = (int) $quantity;

            if ($productId <= 0 || $quantity <= 0) {
                continue;
            }

            $orderItem = $order->items->firstWhere('product_id', $productId);
            if (! $orderItem) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected items do not belong to this order.',
                ]);
            }

            $remaining = $this->remainingReturnQuantity($orderItem);
            if ($quantity > $remaining) {
                throw ValidationException::withMessages([
                    'items' => "You can only return {$remaining} unit(s) for {$orderItem->product_name}.",
                ]);
            }

            if (! $orderItem->product?->is_returnable) {
                throw ValidationException::withMessages([
                    'items' => "{$orderItem->product_name} is not returnable.",
                ]);
            }

            $requested[] = [
                'product_id' => $productId,
                'quantity'   => $quantity,
            ];
        }

        return $requested;
    }

    public function remainingReturnQuantity(OrderItem $orderItem): int
    {
        $reserved = ReturnItem::query()
            ->where('product_id', $orderItem->product_id)
            ->whereHas('returnRequest', function ($query) use ($orderItem) {
                $query->where('order_id', $orderItem->order_id)
                      ->whereIn('status', ['pending', 'approved', 'refund_processing', 'completed']);
            })
            ->sum('quantity');

        return max(0, (int) $orderItem->quantity - (int) $orderItem->returned_quantity - (int) $reserved);
    }

    private function transition(ReturnRequest $return, string $status, ?string $notes = null, ?User $actor = null, bool $force = false): ReturnRequest
    {
        $allowed = [
            'pending'            => ['approved', 'rejected'],
            'approved'           => ['refund_processing', 'completed'],
            'refund_processing'  => ['completed'],
            'rejected'           => [],
            'completed'          => [],
        ];

        if (! $force && ! in_array($status, $allowed[$return->status] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot transition return from {$return->status} to {$status}.",
            ]);
        }

        return DB::transaction(function () use ($return, $status, $notes, $actor) {
            $payload = ['status' => $status];

            if ($notes !== null) {
                $payload['admin_notes'] = $notes;
            }

            if (in_array($status, ['approved', 'rejected', 'completed'], true)) {
                $payload['processed_at'] = now();
            }

            $return->update($payload);

            if ($status === 'approved') {
                $this->restoreStock($return);
                $this->markOrderItemsReturned($return);
                $this->setOrderStatus($return, Order::STATUS_RETURNED, $actor?->id, 'Return approved and stock restored.');
            }

            if ($status === 'rejected') {
                $this->setOrderStatus($return, $return->order->status, $actor?->id, 'Return rejected.');
            }

            if ($status === 'completed') {
                $this->setOrderStatus($return, Order::STATUS_REFUNDED, $actor?->id, 'Return completed and refund settled.');
            }

            $this->notifyParties($return->fresh(['order', 'user', 'reason', 'items.product', 'refund']), $status, $notes);

            return $return->fresh(['order', 'user', 'reason', 'items.product', 'refund']);
        });
    }

    private function restoreStock(ReturnRequest $return): void
    {
        $return->loadMissing('order.items.product');

        foreach ($return->items as $returnItem) {
            $orderItem = $return->order->items->firstWhere('product_id', $returnItem->product_id);
            if (! $orderItem?->product) {
                continue;
            }

            $this->stock->restoreStock(
                product: $orderItem->product,
                qty: $returnItem->quantity,
                reason: 'return',
                orderId: $return->order_id,
                returnId: $return->id,
                initiatedBy: null,
            );
        }
    }

    private function markOrderItemsReturned(ReturnRequest $return): void
    {
        $return->loadMissing('items');

        foreach ($return->items as $returnItem) {
            $orderItem = $return->order->items()->where('product_id', $returnItem->product_id)->first();
            if (! $orderItem) {
                continue;
            }

            $orderItem->increment('returned_quantity', $returnItem->quantity);
        }
    }

    private function setOrderStatus(ReturnRequest $return, string $status, ?int $actorId, string $note): void
    {
        $return->order->update(['status' => $status]);

        OrderStatusHistory::create([
            'order_id'   => $return->order_id,
            'status'     => $status,
            'notes'      => $note,
            'changed_by' => $actorId,
        ]);
    }

    private function notifyParties(ReturnRequest $return, string $event, ?string $notes = null): void
    {
        $userRecipients = collect([$return->user])->filter();
        $vendorRecipient = $return->order?->vendor?->author;
        $adminRecipients = User::query()->where('role', 'admin')->get();

        $notification = new ReturnLifecycleNotification($return, $event, $notes);

        Notification::send($userRecipients, $notification);

        if ($vendorRecipient) {
            $vendorRecipient->notify(new ReturnLifecycleNotification($return, $event, $notes));
        }

        Notification::send($adminRecipients, new ReturnLifecycleNotification($return, $event, $notes));
    }
}