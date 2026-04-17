<?php

namespace App\Services\Api\V1;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderApiService
{
    public function listForActor(User $actor, array $filters, int $limit): LengthAwarePaginator
    {
        $query = Order::query()->with(['user:id,name,email', 'vendor:id,title,author_id']);

        if ($actor->role === 'admin') {
            // No ownership scope for admin.
        } elseif ($actor->role === 'vendor') {
            $vendorId = (int) optional($actor->vendor)->id;
            $query->forVendor($vendorId);
        } else {
            $query->forCustomer($actor->id);
        }

        if (! empty($filters['search'])) {
            $term = (string) $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('order_number', 'like', '%' . $term . '%')
                    ->orWhere('id', $term)
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%' . $term . '%'))
                    ->orWhereHas('vendor', fn ($vq) => $vq->where('title', 'like', '%' . $term . '%'));
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['dispute_status'])) {
            $query->where('dispute_status', $filters['dispute_status']);
        }

        if (! empty($filters['min_total'])) {
            $query->where('total', '>=', (float) $filters['min_total']);
        }

        if (! empty($filters['max_total'])) {
            $query->where('total', '<=', (float) $filters['max_total']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($limit);
    }
}
