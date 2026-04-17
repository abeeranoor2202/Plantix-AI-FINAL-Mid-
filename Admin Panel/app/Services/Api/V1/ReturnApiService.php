<?php

namespace App\Services\Api\V1;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\Shared\ReturnService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReturnApiService
{
    public function __construct(private readonly ReturnService $returns) {}

    public function listForActor(User $actor, array $filters, int $limit): LengthAwarePaginator
    {
        $query = ReturnRequest::query()->with([
            'order:id,order_number,user_id,vendor_id,status,total',
            'user:id,name,email',
            'reason:id,name',
            'items:id,return_id,product_id,quantity',
            'items.product:id,name',
        ]);

        if ($actor->role === 'vendor') {
            $vendorId = (int) optional($actor->vendor)->id;
            $query->whereHas('order', fn ($q) => $q->where('vendor_id', $vendorId));
        } elseif ($actor->role !== 'admin') {
            $query->where('user_id', $actor->id);
        }

        if (! empty($filters['search'])) {
            $term = (string) $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->whereHas('order', fn ($oq) => $oq->where('order_number', 'like', '%' . $term . '%'))
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%' . $term . '%'))
                    ->orWhere('notes', 'like', '%' . $term . '%');
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

    public function createForCustomer(User $actor, Order $order, array $payload): ReturnRequest
    {
        return $this->returns->createReturn($actor, $order, $payload);
    }

    public function transition(ReturnRequest $return, string $status, ?string $notes, User $actor): ReturnRequest
    {
        return match ($status) {
            'approved' => $this->returns->approve($return, $notes, $actor),
            'rejected' => $this->returns->reject($return, $notes ?? 'Rejected by admin.', $actor),
            'refund_processing' => $this->returns->markRefundProcessing($return, $actor),
            'completed' => $this->returns->complete($return, $notes, $actor),
            default => $this->returns->forceStatus($return, $status, $notes, $actor),
        };
    }
}
