<?php

namespace App\Services\Shared;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProductReviewEligibilityService
{
    /**
     * Required function: canReview(user, product)
     * Returns true only when user has at least one delivered order containing product.
     */
    public function canReview(User $user, Product $product): bool
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();
    }

    /**
     * Eligible delivered orders for review dropdown.
     *
     * @return Collection<int, Order>
     */
    public function eligibleOrders(User $user, Product $product): Collection
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->latest()
            ->get(['id', 'order_number', 'status', 'created_at']);
    }

    public function isOrderEligible(User $user, Product $product, int $orderId): bool
    {
        return Order::query()
            ->where('id', $orderId)
            ->where('user_id', $user->id)
            ->where('status', Order::STATUS_DELIVERED)
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->exists();
    }
}

