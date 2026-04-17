<?php

namespace App\Services\Api\V1;

use App\Models\Order;
use App\Models\OrderDispute;
use App\Models\User;
use App\Notifications\Order\OrderDisputeResolvedNotification;
use App\Notifications\Order\OrderDisputeResponseNotification;
use App\Notifications\Order\OrderDisputeSubmittedNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DisputeApiService
{
    public function listForActor(User $actor, array $filters, int $limit): LengthAwarePaginator
    {
        $query = OrderDispute::query()->with([
            'order:id,order_number,user_id,vendor_id,dispute_status,status,total',
            'user:id,name,email',
            'vendor:id,title,author_id',
            'resolver:id,name,email',
        ]);

        if ($actor->role === 'vendor') {
            $vendorId = (int) optional($actor->vendor)->id;
            $query->where('vendor_id', $vendorId);
        } elseif ($actor->role !== 'admin') {
            $query->where('user_id', $actor->id);
        }

        if (! empty($filters['search'])) {
            $term = (string) $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('reason', 'like', '%' . $term . '%')
                    ->orWhere('escalation_reason', 'like', '%' . $term . '%')
                    ->orWhereHas('order', fn ($oq) => $oq->where('order_number', 'like', '%' . $term . '%'));
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($limit);
    }

    public function open(User $actor, Order $order, string $reason): OrderDispute
    {
        return DB::transaction(function () use ($actor, $order, $reason): OrderDispute {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $lockedOrder->canOpenDispute()) {
                throw new \DomainException('This order cannot be disputed at its current stage.');
            }

            if ($lockedOrder->hasOpenDispute()) {
                throw new \DomainException('This order already has an active dispute.');
            }

            $dispute = OrderDispute::updateOrCreate(
                ['order_id' => $lockedOrder->id],
                [
                    'user_id' => $actor->id,
                    'vendor_id' => $lockedOrder->vendor_id,
                    'status' => Order::DISPUTE_PENDING,
                    'reason' => $reason,
                    'escalation_reason' => null,
                    'escalated_at' => null,
                    'responded_at' => null,
                    'resolved_at' => null,
                    'admin_notes' => null,
                    'resolved_by' => null,
                    'refund_escalated_at' => null,
                    'refund_reference' => null,
                ]
            );

            $lockedOrder->update([
                'dispute_status' => Order::DISPUTE_PENDING,
                'disputed_at' => now(),
                'dispute_reason' => $reason,
            ]);

            if ($lockedOrder->vendor?->author) {
                $lockedOrder->vendor->author->notify(new OrderDisputeSubmittedNotification(
                    $lockedOrder,
                    $reason,
                    route('vendor.orders.show', $lockedOrder->id)
                ));
            }

            $admins = User::query()->where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new OrderDisputeSubmittedNotification(
                    $lockedOrder,
                    $reason,
                    route('admin.orders.show', $lockedOrder->id)
                ));
            }

            return $dispute->fresh(['order', 'user', 'vendor']);
        });
    }

    public function escalate(User $actor, Order $order, string $escalationReason): OrderDispute
    {
        return DB::transaction(function () use ($actor, $order, $escalationReason): OrderDispute {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $dispute = OrderDispute::query()->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($dispute->status !== Order::DISPUTE_VENDOR_RESPONDED) {
                throw new \DomainException('Only vendor-responded disputes can be escalated.');
            }

            $dispute->update([
                'status' => Order::DISPUTE_ESCALATED,
                'escalation_reason' => $escalationReason,
                'escalated_at' => now(),
            ]);

            $lockedOrder->update([
                'dispute_status' => Order::DISPUTE_ESCALATED,
                'dispute_reason' => $escalationReason,
            ]);

            $admins = User::query()->where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new OrderDisputeSubmittedNotification(
                    $lockedOrder,
                    'Escalated by customer: ' . $escalationReason,
                    route('admin.orders.show', $lockedOrder->id)
                ));
            }

            return $dispute->fresh(['order', 'user', 'vendor']);
        });
    }

    public function vendorRespond(User $actor, Order $order, string $response): OrderDispute
    {
        return DB::transaction(function () use ($actor, $order, $response): OrderDispute {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $dispute = OrderDispute::query()->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($dispute->status, [Order::DISPUTE_PENDING, Order::DISPUTE_ESCALATED], true)) {
                throw new \DomainException('This dispute is no longer open for vendor response.');
            }

            $dispute->update([
                'vendor_response' => $response,
                'status' => Order::DISPUTE_VENDOR_RESPONDED,
                'responded_at' => now(),
            ]);

            $lockedOrder->update([
                'dispute_status' => Order::DISPUTE_VENDOR_RESPONDED,
                'vendor_dispute_response' => $response,
            ]);

            if ($lockedOrder->user) {
                $lockedOrder->user->notify(new OrderDisputeResponseNotification(
                    $lockedOrder,
                    $response,
                    route('order.details', $lockedOrder->id)
                ));
            }

            $admins = User::query()->where('role', 'admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new OrderDisputeResponseNotification(
                    $lockedOrder,
                    $response,
                    route('admin.orders.show', $lockedOrder->id)
                ));
            }

            return $dispute->fresh(['order', 'user', 'vendor']);
        });
    }

    public function resolve(User $admin, Order $order, string $status, string $resolution): OrderDispute
    {
        return DB::transaction(function () use ($admin, $order, $status, $resolution): OrderDispute {
            $lockedOrder = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            $dispute = OrderDispute::query()->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($dispute->status, [Order::DISPUTE_PENDING, Order::DISPUTE_VENDOR_RESPONDED, Order::DISPUTE_ESCALATED], true)) {
                throw new \DomainException('Only active disputes can be resolved.');
            }

            if (! in_array($status, [Order::DISPUTE_RESOLVED, Order::DISPUTE_REJECTED, Order::DISPUTE_CANCELLED], true)) {
                throw new \DomainException('Invalid dispute resolution status.');
            }

            $dispute->update([
                'status' => $status,
                'admin_notes' => $resolution,
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ]);

            $lockedOrder->update([
                'dispute_status' => $status,
                'dispute_resolved_by' => $admin->id,
                'dispute_resolved_at' => now(),
                'dispute_admin_notes' => $resolution,
            ]);

            $urlForCustomer = route('order.details', $lockedOrder->id);
            $urlForVendor = route('vendor.orders.show', $lockedOrder->id);

            if ($lockedOrder->user) {
                $lockedOrder->user->notify(new OrderDisputeResolvedNotification($lockedOrder, $resolution, $status, $urlForCustomer));
            }

            if ($lockedOrder->vendor?->author) {
                $lockedOrder->vendor->author->notify(new OrderDisputeResolvedNotification($lockedOrder, $resolution, $status, $urlForVendor));
            }

            return $dispute->fresh(['order', 'user', 'vendor', 'resolver']);
        });
    }
}
